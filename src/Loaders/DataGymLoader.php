<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Loaders;

use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Utils\EncoderUtil;

final class DataGymLoader extends Loader
{
    /**
     * @return non-empty-array<int|string, int>
     *
     * @throws \Rahul900day\Tiktoken\Exceptions\InvalidChecksumException
     */
    public function load(
        string $vocabBpeFile,
        string $encoderJsonFile,
        ?string $vocabBpeHash = null,
        ?string $encoderJsonHash = null
    ): array {
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

    private function createByteRange(string $startChar, string $endChar): array
    {
        return range(mb_ord($startChar), mb_ord($endChar));
    }

    private function byteToCharMap(array $byteArray): array
    {
        $byteToCharMap = [];

        foreach ($byteArray as $byte) {
            $byteToCharMap[mb_chr($byte)] = $byte;
        }

        return $byteToCharMap;
    }

    private function addBytesNotInRank(array &$rankToIntByte, array &$dataGymByteToByteMap): void
    {
        $unicodeCounter = 0;

        foreach (range(0, 255) as $byte) {
            if (in_array($byte, $rankToIntByte)) {
                continue;
            }

            $rankToIntByte[] = $byte;
            $dataGymByteToByteMap[mb_chr(2 ** 8 + $unicodeCounter)] = $byte;

            $unicodeCounter++;
        }
    }

    private function createBpeMerges(string $vocabBpeContents): array
    {
        return array_map(fn ($mergeStr): array => explode(' ', $mergeStr), array_slice(explode("\n", $vocabBpeContents), 1, -1));
    }

    private function createBpeRanks(array $rankToIntByte): array
    {
        $bpeRanks = [];
        foreach ($rankToIntByte as $i => $byte) {
            $bpeRanks[EncoderUtil::fromBytes([$byte])] = $i;
        }

        return $bpeRanks;
    }

    private function addMergeRanksToBpe(array $bpeMerges, array &$bpeRanks, array $dataGymByteToByteMap): void
    {
        foreach ($bpeMerges as [$first, $second]) {

            $tokenBytes = [
                ...$this->decodeDataGym($first, $dataGymByteToByteMap),
                ...$this->decodeDataGym($second, $dataGymByteToByteMap),
            ];
            $token = EncoderUtil::fromBytes($tokenBytes);

            $bpeRanks[$token] = count($bpeRanks);
        }
    }

    private function loadEncoderJson(string $encoderJsonFile, ?string $encoderJsonHash, array $dataGymByteToByteMap): array
    {
        /** @var non-empty-array<string, int> $encoderJson */
        $encoderJson = json_decode($this->readFileCached($encoderJsonFile, $encoderJsonHash), true);
        $encoderJsonLoaded = [];
        foreach ($encoderJson as $key => $value) {
            $token = EncoderUtil::fromBytes($this->decodeDataGym($key, $dataGymByteToByteMap));
            $encoderJsonLoaded[$token] = $value;
        }

        unset($encoderJsonLoaded[SpecialToken::ENDOFTEXT->value]);
        unset($encoderJsonLoaded[SpecialToken::STARTOFTEXT->value]);

        return $encoderJsonLoaded;
    }

    private function validateBpeAndEncoderJsonRanks(array $bpeRanks, array $encoderJson): void
    {
        if ($bpeRanks !== $encoderJson) {
            throw new \Exception("BPE Ranks & Encoder JSON Ranks Doesn't Match");
        }
    }

    private function decodeDataGym(string|int $value, array $dataGymByteToByte): array
    {
        $bytes = [];
        $value = strval($value);

        foreach (mb_str_split($value) as $b) {
            $bytes[] = $dataGymByteToByte[$b];
        }

        return $bytes;
    }
}
