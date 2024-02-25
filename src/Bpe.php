<?php

namespace Rahul900day\Tiktoken;

use Exception;
use InvalidArgumentException;
use Rahul900day\Tiktoken\Utils\EncoderUtils;

class Bpe
{
    protected string $specialRegex;

    public function __construct(
        protected array  $ranks,
        protected array  $specialTokens,
        protected string $regex,
    )
    {
        $parts = array_map('preg_quote', array_keys($specialTokens));
        $this->specialRegex = '/'. implode('|', $parts) .'/u';

        if (false === preg_match($this->specialRegex, null)) {
            throw new Exception("Invalid regex pattern: {$this->specialRegex}");
        }
    }

    public function encode(string $text, array $allowedSpecial)
    {
        $ret = [];

        $start = 0;
        $last_piece_token_len = 0;
        while (true) {
            $start_find = $start;
            $next_special  = null;
            while (true) {
                // Find the next allowed special token, if any
                if (preg_match($this->specialRegex, mb_substr($text, $start_find), $matches, PREG_OFFSET_CAPTURE)) {
                    /** @var array $next_special */
                    $next_special = $matches[0];
                    $match_str = mb_substr($text, $start_find + $next_special[1], mb_strlen($next_special[0]));

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
                    $piece = EncoderUtils::toBytes($match);

                    if ($token = $this->getToken($match)) {
                        $last_piece_token_len = 1;
                        $ret[] = $token;
                        continue;
                    }

                    $tokens = $this->bpe($piece);
                    $last_piece_token_len = count($tokens);
                    foreach ($tokens as $token) {
                        $ret[] = $token;
                    }
                }
            }

            if ($next_special) {
                // And here we push the special token
                $piece = $next_special[0];
                $token = $this->specialTokens[$piece];
                $ret[] = $token;
                $start = $next_special[1] + strlen($next_special[0]);
                $last_piece_token_len = 0;
            } else {
                break;
            }
        }

        // last_piece_token_len is how many tokens came from the last regex split. This is used
        // for determining unstable tokens, since you can't merge across (stable) regex splits
        return [$ret, $last_piece_token_len];
    }

    protected function bpe(array $piece): array
    {
        $parts = [];

        $min_rank = [PHP_INT_MAX, PHP_INT_MAX];

        foreach (range(0, count($piece) - 2) as $i) {
            $rank = $this->getToken($this->getSegment($piece, $i, $i+2)) ?? PHP_INT_MAX;

            if ($rank < $min_rank[0]) {
                $min_rank = [$rank, $i];
            }

            $parts[] = [$i, $rank];
        }

        $parts[] = [count($piece) - 1, PHP_INT_MAX];
        $parts[] = [count($piece), PHP_INT_MAX];

        $get_rank = function(array $parts, int $startIndex) use($piece) {
            if ($startIndex + 3 >= count($parts)) {
                return PHP_INT_MAX;
            }

            $start = $this->getNthItem($parts, $startIndex)[0];
            $stop = $this->getNthItem($parts, $startIndex + 3)[0];
            return $this->getToken($this->getSegment($piece, $start, $stop)) ?? PHP_INT_MAX;
        };

        while ($min_rank[0] !== PHP_INT_MAX) {
            $partIndex = $min_rank[1];

            if ($partIndex > 0) {
                $this->getNthItem($parts, $partIndex - 1)[1] = $get_rank($parts, $partIndex - 1);
            }


            $this->getNthItem($parts, $partIndex)[1] = $get_rank($parts, $partIndex);
            $this->unsetNthItem($parts, $partIndex + 1);

            $min_rank = [PHP_INT_MAX, PHP_INT_MAX];
            foreach ($this->getSegment($parts, 0, count($parts) - 1) as $i => [$_, $rank]) {
                if ($rank < $min_rank[0]) {
                    $min_rank = [$rank, $i];
                }
            }
        }

        return array_map(function ($pair) use ($piece) {
            $pairs = $this->getSegment($piece, $pair[0][0], $pair[1][0]);

            return $this->getToken(EncoderUtils::fromBytes($pairs));

        }, $this->getPairs($parts));
    }

    protected function getPairs(array $parts): array
    {
        $pairs = [];
        $previousPart = array_shift($parts);

        foreach ($parts as $part) {
            $pairs[] = [$previousPart, $part];
            $previousPart = $part;
        }

        return $pairs;
    }

    protected function getToken(array|string $bytes): ?int
    {
        if(is_array($bytes)) {
            $bytes = EncoderUtils::fromBytes($bytes);
        }

        return $this->ranks[$bytes] ?? null;
    }

    protected function getSegment(array $array, int $start, int $end): array
    {
        if($end <= $start) {
            throw new Exception("Cannot create segment when start: [{$start}] is less than or equals to end: [{$end}]");
        }

        return array_slice($array, $start, $end - $start);
    }

    protected function &getNthItem(&$array, $nth): mixed
    {
        $key = array_keys($array)[$nth];

        return $array[$key];
    }

    protected function unsetNthItem(&$array, $nth): void
    {
        $key = array_keys($array)[$nth];

        unset($array[$key]);
    }
}