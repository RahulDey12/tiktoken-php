<?php

namespace Rahul900day\Tiktoken\Exceptions;

use Exception;

class SpecialTokenNotAllowedException extends Exception
{
    public function __construct(string $specialToken)
    {
        parent::__construct(
            "The text contains a special token that is not allowed: {$specialToken}",
        );
    }
}
