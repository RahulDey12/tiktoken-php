<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Exceptions;

class InvalidModelNameException extends TiktokenException
{
    public function __construct(string $modelName)
    {
        parent::__construct(
            "Could not automatically map {$modelName} to a tokenizer."
        );
    }
}
