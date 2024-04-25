<?php

namespace Rahul900day\Tiktoken\Exceptions;

use Exception;

class RankNotFoundException extends Exception
{
    public function __construct(int $rank)
    {
        parent::__construct("Unable to find rank: [{$rank}]");
    }
}
