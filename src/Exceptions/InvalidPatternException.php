<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Exceptions;

use Exception;

class InvalidPatternException extends Exception
{
    public function __construct(string $pattern)
    {
        parent::__construct(
            "The regex pattern '{$pattern}' is invalid."
        );
    }
}
