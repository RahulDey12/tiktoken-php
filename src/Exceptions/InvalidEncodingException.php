<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Exceptions;

use Exception;

class InvalidEncodingException extends Exception
{
    public function __construct(string $encodingName)
    {
        parent::__construct(
            "The encoding '{$encodingName}' is not valid."
        );
    }
}
