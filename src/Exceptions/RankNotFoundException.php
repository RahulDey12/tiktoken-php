<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Exceptions;

class RankNotFoundException extends TiktokenException
{
    public function __construct(int $rank)
    {
        parent::__construct("Unable to find rank: [{$rank}]");
    }
}
