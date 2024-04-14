<?php

namespace Rahul900day\Tiktoken\Loaders;

use Rahul900day\Tiktoken\Utils\EncoderUtil;

class DataGymLoader extends Loader
{
    public function load(
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
            $bpeRanks[EncoderUtil::fromBytes([$byte])] = $i;
        }
        return $bpeRanks;
    }

    protected function addMergeRanksToBpe(array $bpeMerges, array &$bpeRanks, array $dataGymByteToByteMap): void {
        foreach ($bpeMerges as [$first, $second]) {

            $tokenBytes = [
                ...$this->decodeDataGym($first, $dataGymByteToByteMap),
                ...$this->decodeDataGym($second, $dataGymByteToByteMap)
            ];
            $token = EncoderUtil::fromBytes($tokenBytes);

            $bpeRanks[$token] = count($bpeRanks);
        }
    }

    protected function loadEncoderJson(string $encoderJsonFile, ?string $encoderJsonHash, array $dataGymByteToByteMap): array {
        $encoderJson = json_decode($this->readFileCached($encoderJsonFile, $encoderJsonHash), true);
        $encoderJsonLoaded = [];
        foreach ($encoderJson as $key => $value) {
            $token = EncoderUtil::fromBytes($this->decodeDataGym($key, $dataGymByteToByteMap));
            $encoderJsonLoaded[$token] = $value;
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

    protected function decodeDataGym(string|int $value, array $dataGymByteToByte): array
    {
        $bytes = [];
        $value = strval($value);

        foreach (mb_str_split($value) as $b) {
            $bytes[] = $dataGymByteToByte[$b];
        }

        return $bytes;
    }
}