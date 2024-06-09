<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Countable;
use Rahul900day\Tiktoken\Exceptions\RankNotFoundException;

final class Vocab implements Countable
{
    /**
     * @var non-empty-array<int, string>
     */
    public readonly array $rankToTokens;

    /**
     * @param  non-empty-array<string|int, int>  $tokenToRanks
     */
    public function __construct(public readonly array $tokenToRanks)
    {
        $this->rankToTokens = array_map('strval', array_flip($this->tokenToRanks));
    }

    public function getRank(string $token): ?int
    {
        return $this->tokenToRanks[$token] ?? null;
    }

    public function getToken(int $rank): string
    {
        return $this->rankToTokens[$rank] ?? throw new RankNotFoundException($rank);
    }

    public function count(): int
    {
        return count($this->tokenToRanks);
    }
}
