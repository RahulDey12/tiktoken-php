<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Exception;
use Rahul900day\Tiktoken\Contracts\BpeContract;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Utils\ArrayUtil;
use Rahul900day\Tiktoken\Utils\EncoderUtil;

final class Bpe implements BpeContract
{
    public const MAX_INT = PHP_INT_MAX;

    private readonly string $specialRegex;

    /**
     * @param  array<string, int>  $specialTokens
     *
     * @throws Exceptions\InvalidPatternException
     */
    public function __construct(
        private readonly Vocab $vocab,
        private readonly array $specialTokens,
        private readonly string $regex,
    ) {
        $this->specialRegex = SpecialToken::getRegex(array_keys($specialTokens));
    }

    /**
     * {@inheritDoc}
     */
    public function encode(string $text, array $allowedSpecial): array
    {
        $ranks = [];

        $start = 0;
        $last_piece_token_len = 0;
        while (true) {
            $start_find = $start;
            $next_special = null;
            while (true) {
                // Find the next allowed special rank, if any
                if (preg_match($this->specialRegex, mb_substr($text, $start_find), $matches, PREG_OFFSET_CAPTURE)) {
                    /** @var array{0: string, 1: int} $next_special */
                    $next_special = $matches[0];
                    $match_str = mb_substr($text, $start_find + $next_special[1], mb_strlen((string) $next_special[0]));

                    if (in_array($match_str, $allowedSpecial)) {
                        break;
                    }

                    $start_find = $next_special[1] + 1;
                } else {
                    break;
                }
            }

            $end = $next_special ? $next_special[1] : mb_strlen($text);

            // Okay, here we go, compare this logic to _encode_ordinary_native
            if (preg_match_all($this->regex, mb_substr($text, $start_find, $end - $start_find), $matches)) {
                foreach ($matches[0] as $match) {
                    $bytes = EncoderUtil::toBytes($match);

                    if ($rank = $this->getRank($match)) {
                        $last_piece_token_len = 1;
                        $ranks[] = $rank;

                        continue;
                    }

                    $encodedTokens = $this->bpe($bytes);
                    $ranks = [...$ranks, ...$encodedTokens];

                    $last_piece_token_len = count($encodedTokens);
                }
            }

            if ($next_special) {
                // And here we push the special rank
                $piece = $next_special[0];
                $rank = $this->specialTokens[$piece];
                $ranks[] = $rank;
                $start = $next_special[1] + strlen((string) $next_special[0]);
                $last_piece_token_len = 0;
            } else {
                break;
            }
        }

        // last_piece_token_len is how many ranks came from the last regex split. This is used
        // for determining unstable ranks, since you can't merge across (stable) regex splits
        return [$ranks, $last_piece_token_len];
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function encodeOrdinary(string $text): array
    {
        $ranks = [];

        preg_match_all($this->regex, $text, $matches);

        foreach ($matches[0] as $match) {
            $bytes = EncoderUtil::toBytes($match);

            if ($rank = $this->getRank($match)) {
                $ranks[] = $rank;

                continue;
            }

            $ranks = [...$ranks, ...$this->bpe($bytes)];
        }

        return $ranks;
    }

    /**
     * @param int[] $bytes
     * @return int[]
     * @throws Exception
     */
    private function bpe(array $bytes): array
    {
        $bytePairs = $this->initializePairs($bytes);
        $minRank = $this->getMinRankPair($bytePairs);

        while ($minRank[0] !== self::MAX_INT) {
            $index = $minRank[1];

            if ($index > 0) {
                // @phpstan-ignore-next-line
                ArrayUtil::at($bytePairs, $index - 1)[1] = $this->calculateMergedRank($bytes, $bytePairs, $index - 1);
            }

            // @phpstan-ignore-next-line
            ArrayUtil::at($bytePairs, $index)[1] = $this->calculateMergedRank($bytes, $bytePairs, $index);
            ArrayUtil::unsetAt($bytePairs, $index + 1);

            // @phpstan-ignore-next-line
            $minRank = $this->getMinRankPair(ArrayUtil::getSegment($bytePairs, 0, count($bytePairs) - 1));
        }

        return array_map(function ($pair) use ($bytes): int {
            $pairs = ArrayUtil::getSegment($bytes, $pair[0][0], $pair[1][0]);

            return $this->getRank($pairs) ?? throw new Exception('Token cannot be found for: '.implode(',', $pairs));

        }, $this->getAllPairs($bytePairs));
    }

    /**
     * @param int[] $bytes
     * @return array<array{0: int, 1: int}>
     */
    private function initializePairs(array $bytes): array
    {
        $parts = [];

        foreach (range(0, count($bytes) - 2) as $index) {
            $segment = ArrayUtil::getSegment($bytes, $index, $index + 2);
            $rank = $this->getRank($segment) ?? self::MAX_INT;

            $parts[] = [$index, $rank];
        }

        $parts[] = [count($bytes) - 1, self::MAX_INT];
        $parts[] = [count($bytes), self::MAX_INT];

        return $parts;
    }

    /**
     * @param array<array{0: int, 1: int}> $parts
     * @return array{0: int, 1: int}
     */
    private function getMinRankPair(array $parts): array
    {
        $minRank = [self::MAX_INT, self::MAX_INT];

        foreach ($parts as $index => [$_, $rank]) {
            if ($rank < $minRank[0]) {
                $minRank = [$rank, $index];
            }
        }
        return $minRank;
    }

    /**
     * @param int[] $bytes
     * @param array<array{0: int, 1: int}> $parts
     * @param int $startIndex
     * @return int
     */
    private function calculateMergedRank(array $bytes, array $parts, int $startIndex): int
    {
        if ($startIndex + 3 >= count($parts)) {
            return self::MAX_INT;
        }

        /** @var array{0: int, 1: int} $startPart */
        $startPart = ArrayUtil::at($parts, $startIndex);
        /** @var array{0: int, 1: int} $stopPart */
        $stopPart = ArrayUtil::at($parts, $startIndex + 3); // @phpstan-ignore-line

        $start = $startPart[0];
        $stop = $stopPart[0];

        return $this->getRank(ArrayUtil::getSegment($bytes, $start, $stop)) ?? self::MAX_INT;
    }

    /**
     * @param non-empty-array<int[]> $parts
     * @return array<array<int[]>>
     */
    private function getAllPairs(array $parts): array
    {
        $pairs = [];
        /** @var int[] $previousPart */
        $previousPart = array_shift($parts);

        foreach ($parts as $part) {
            $pairs[] = [$previousPart, $part];
            $previousPart = $part;
        }

        return $pairs;
    }

    /**
     * @param int[]|string $bytes
     * @return int|null
     */
    private function getRank(array|string $bytes): ?int
    {
        if (is_array($bytes)) {
            $bytes = EncoderUtil::fromBytes($bytes);
        }

        return $this->vocab->getRank($bytes);
    }
}
