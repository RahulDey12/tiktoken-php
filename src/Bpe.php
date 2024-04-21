<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Exception;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Utils\ArrayUtil;
use Rahul900day\Tiktoken\Utils\EncoderUtil;

final class Bpe
{
    public const MAX_INT = PHP_INT_MAX;

    private string $specialRegex;

    public function __construct(
        private readonly Vocab $vocab,
        private readonly array $specialTokens,
        private readonly string $regex,
    ) {
        $this->specialRegex = SpecialToken::getRegex(array_keys($specialTokens));
    }

    public function encode(string $text, array $allowedSpecial): array
    {
        $tokens = [];

        $start = 0;
        $last_piece_token_len = 0;
        while (true) {
            $start_find = $start;
            $next_special = null;
            while (true) {
                // Find the next allowed special token, if any
                if (preg_match($this->specialRegex, mb_substr($text, $start_find), $matches, PREG_OFFSET_CAPTURE)) {
                    /** @var array $next_special */
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

                    if ($token = $this->getToken($match)) {
                        $last_piece_token_len = 1;
                        $tokens[] = $token;

                        continue;
                    }

                    $encodedTokens = $this->bpe($bytes);
                    $tokens = [...$tokens, ...$encodedTokens];

                    $last_piece_token_len = count($encodedTokens);
                }
            }

            if ($next_special) {
                // And here we push the special token
                $piece = $next_special[0];
                $token = $this->specialTokens[$piece];
                $tokens[] = $token;
                $start = $next_special[1] + strlen((string) $next_special[0]);
                $last_piece_token_len = 0;
            } else {
                break;
            }
        }

        // last_piece_token_len is how many tokens came from the last regex split. This is used
        // for determining unstable tokens, since you can't merge across (stable) regex splits
        return [$tokens, $last_piece_token_len];
    }

    public function encodeOrdinary(string $text): array
    {
        $tokens = [];

        preg_match_all($this->regex, $text, $matches);

        foreach ($matches[0] as $match) {
            $bytes = EncoderUtil::toBytes($match);

            if ($token = $this->getToken($match)) {
                $tokens[] = $token;

                continue;
            }

            $tokens = [...$tokens, ...$this->bpe($bytes)];
        }

        return $tokens;
    }

    private function bpe(array $bytes): array
    {
        $parts = $this->initializeParts($bytes);
        $minRank = $this->getMinRank($parts);

        while ($minRank[0] !== self::MAX_INT) {
            $partIndex = $minRank[1];

            if ($partIndex > 0) {
                ArrayUtil::nthItem($parts, $partIndex - 1)[1] = $this->getRank($bytes, $parts, $partIndex - 1);
            }

            ArrayUtil::nthItem($parts, $partIndex)[1] = $this->getRank($bytes, $parts, $partIndex);
            ArrayUtil::unsetNthItem($parts, $partIndex + 1);

            $minRank = $this->getMinRank(ArrayUtil::getSegment($parts, 0, count($parts) - 1));
        }

        return array_map(function ($pair) use ($bytes): int {
            $pairs = ArrayUtil::getSegment($bytes, $pair[0][0], $pair[1][0]);

            return $this->getToken($pairs) ?? throw new Exception('Token cannot be found for: '.implode(',', $pairs));

        }, $this->getPairs($parts));
    }

    private function initializeParts(array $bytes): array
    {
        $parts = [];

        foreach (range(0, count($bytes) - 2) as $index) {
            $segment = ArrayUtil::getSegment($bytes, $index, $index + 2);
            $rank = $this->getToken($segment) ?? self::MAX_INT;

            $parts[] = [$index, $rank];
        }

        $parts[] = [count($bytes) - 1, self::MAX_INT];
        $parts[] = [count($bytes), self::MAX_INT];

        return $parts;
    }

    private function getMinRank(array $parts): array
    {
        $minRank = [self::MAX_INT, self::MAX_INT];

        foreach ($parts as $index => [$_, $rank]) {
            if ($rank < $minRank[0]) {
                $minRank = [$rank, $index];
            }
        }

        return $minRank;
    }

    private function getRank(array $bytes, array $parts, int $startIndex): int
    {
        if ($startIndex + 3 >= count($parts)) {
            return self::MAX_INT;
        }

        $start = ArrayUtil::nthItem($parts, $startIndex)[0];
        $stop = ArrayUtil::nthItem($parts, $startIndex + 3)[0];

        return $this->getToken(ArrayUtil::getSegment($bytes, $start, $stop)) ?? self::MAX_INT;
    }

    private function getPairs(array $parts): array
    {
        $pairs = [];
        $previousPart = array_shift($parts);

        foreach ($parts as $part) {
            $pairs[] = [$previousPart, $part];
            $previousPart = $part;
        }

        return $pairs;
    }

    private function getToken(array|string $bytes): ?int
    {
        if (is_array($bytes)) {
            $bytes = EncoderUtil::fromBytes($bytes);
        }

        return $this->vocab->getRank($bytes);
    }
}
