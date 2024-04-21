<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Countable;

class Vocab implements Countable
{
    public readonly array $rankToTokens;

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
        return $this->rankToTokens[$rank] ?? throw new \Exception("Unable to find rank: [{$rank}]");
    }

    public function count(): int
    {
        return count($this->tokenToRanks);
    }
}
