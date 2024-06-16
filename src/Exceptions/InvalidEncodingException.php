<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Exceptions;

class InvalidEncodingException extends TiktokenException
{
    public function __construct(string $encodingName)
    {
        parent::__construct(
            "The encoding '{$encodingName}' is not valid."
        );
    }
}
