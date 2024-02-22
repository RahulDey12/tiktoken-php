<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Utils;

use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class Loader
{
    protected Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function loadTiktokenRanks(string $tiktokenBpeFile, ?string $expectedHash = null): array
    {
        $contents = $this->readFileCached($tiktokenBpeFile, $expectedHash);
        $result = [];

        foreach (explode("\n", $contents) as $line) {
            [$token, $rank] = explode(' ', $line);
            $result[base64_decode($token)] = intval($rank);
        }

        return $result;
    }

    public function loadDataGymRanks(
        string $vocabBpeFile,
        string $encoderJsonFile,
        ?string $vocabBpeHash = null,
        ?string $encoderJsonHash = null
    ): array
    {
        $asciiByteRange = $this->createByteRange('!', '~');
        $latinByteRange = $this->createByteRange('¡', '¬');
        $extendedLatinByteRange = $this->createByteRange('®', 'ÿ');

        $rankToIntByte = [...$asciiByteRange, ...$latinByteRange, ...$extendedLatinByteRange];

        $dataGymByteToByteMap = $this->byteToCharMap($rankToIntByte);

        $this->addBytesNotInRank($rankToIntByte, $dataGymByteToByteMap);

        $vocabBpeContents = $this->readFileCached($vocabBpeFile, $vocabBpeHash);
        $bpeMerges = $this->createBpeMerges($vocabBpeContents);

        $bpeRanks = $this->createBpeRanks($rankToIntByte);

        $this->addMergeRanksToBpe($bpeMerges, $bpeRanks, $dataGymByteToByteMap);

        $encoderJson = $this->loadEncoderJson($encoderJsonFile, $encoderJsonHash, $dataGymByteToByteMap);

        $this->validateBpeAndEncoderJsonRanks($bpeRanks, $encoderJson);

        return $bpeRanks;
    }

    protected function createByteRange(string $startChar, string $endChar): array {
        return range(mb_ord($startChar), mb_ord($endChar));
    }

    protected function byteToCharMap(array $byteArray): array {
        $byteToCharMap = [];

        foreach ($byteArray as $byte) {
            $byteToCharMap[mb_chr($byte)] = $byte;
        }

        return $byteToCharMap;
    }

    protected function addBytesNotInRank(array &$rankToIntByte, array &$dataGymByteToByteMap): void {
        $unicodeCounter = 0;

        foreach(range(0, 255) as $byte) {
            if (in_array($byte, $rankToIntByte)) {
                continue;
            }

            $rankToIntByte[] = $byte;
            $dataGymByteToByteMap[mb_chr(2**8 + $unicodeCounter)] = $byte;

            $unicodeCounter++;
        }
    }

    protected function createBpeMerges(string $vocabBpeContents): array {
        return array_map(function($mergeStr) {
            return explode(" ", $mergeStr);
        }, array_slice(explode("\n", $vocabBpeContents), 1, -1));
    }

    protected function createBpeRanks(array $rankToIntByte): array {
        $bpeRanks = [];
        foreach ($rankToIntByte as $i => $byte) {
            $bpeRanks[mb_chr($byte)] = $i;
        }
        return $bpeRanks;
    }

    protected function addMergeRanksToBpe(array $bpeMerges, array &$bpeRanks, array $dataGymByteToByteMap): void {
        foreach ($bpeMerges as [$first, $second]) {
            $bpeRanks[$this->decodeDataGym($first, $dataGymByteToByteMap) . $this->decodeDataGym($second, $dataGymByteToByteMap)] = count($bpeRanks);
        }
    }

    protected function loadEncoderJson(string $encoderJsonFile, ?string $encoderJsonHash, array $dataGymByteToByteMap): array {
        $encoderJson = json_decode($this->readFileCached($encoderJsonFile, $encoderJsonHash), true);
        $encoderJsonLoaded = [];
        foreach ($encoderJson as $key => $value) {
            $encoderJsonLoaded[$this->decodeDataGym($key, $dataGymByteToByteMap)] = $value;
        }

        unset($encoderJsonLoaded['<|endoftext|>']);
        unset($encoderJsonLoaded['<|startoftext|>']);

        return $encoderJsonLoaded;
    }

    protected function validateBpeAndEncoderJsonRanks(array $bpeRanks, array $encoderJson): void {
        if($bpeRanks !== $encoderJson) {
            throw new \Exception("BPE Ranks & Encoder JSON Ranks Doesn't Match");
        }
    }

    protected function decodeDataGym(string|int $value, array $dataGymByteToByte): string
    {
        $bytes = [];
        $value = strval($value);

        foreach (mb_str_split($value) as $b) {
            $bytes[] = $dataGymByteToByte[$b];
        }

        return implode(array_map("mb_chr", $bytes));
    }

    public function readFile(string $blobPath): string
    {
        $client = new Client();
        $res = $client->get($blobPath);

        return $res->getBody()->getContents();
    }

    protected function checkHash(string $data, string $hash): bool
    {
        $actual_hash = hash('sha256', $data);

        return $actual_hash === $hash;
    }

    protected function readFileCached(string $blobPath, ?string $expectedHash = null): string
    {
        $user_specified_cache = true;
        if (getenv("TIKTOKEN_CACHE_DIR")) {
            $cache_dir = Path::canonicalize(getenv("TIKTOKEN_CACHE_DIR"));
        } elseif (getenv("DATA_GYM_CACHE_DIR")) {
            $cache_dir = Path::canonicalize(getenv("DATA_GYM_CACHE_DIR"));
        } else {
            $cache_dir = Path::normalize(sys_get_temp_dir(). '/'. 'data-gym-cache');
            $user_specified_cache = false;
        }

        if ($cache_dir === "") {
            return $this->readFile($blobPath);
        }

        $cache_key = sha1($blobPath);

        $cache_path = Path::canonicalize("{$cache_dir}/{$cache_key}");

        if ($this->filesystem->exists($cache_path)) {
            $data = file_get_contents($cache_path);

            if (is_null($expectedHash) || $this->checkHash($data, $expectedHash)) {
                return $data;
            }

            // the cached file does not match the hash, remove it and re-fetch
            $this->filesystem->remove($cache_path);
        }

        $contents = $this->readFile($blobPath);

        if($expectedHash && ! $this->checkHash($contents, $expectedHash)) {
            throw new \Exception("Hash mismatch for data downloaded from {$blobPath} (expected {$expectedHash}). This may indicate a corrupted download. Please try again.");
        }

        $this->filesystem->mkdir($cache_dir);
        $tmp_filename = $this->filesystem->tempnam($cache_dir, $cache_key, '.tmp');
        file_put_contents($tmp_filename, $contents);
        $this->filesystem->rename($tmp_filename, $cache_path);

        return $contents;
    }
}