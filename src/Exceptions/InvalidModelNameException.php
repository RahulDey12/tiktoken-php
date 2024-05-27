<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Exceptions;

use Exception;

class InvalidModelNameException extends Exception
{
    public function __construct(string $modelName)
    {
        parent::__construct(
            "Could not automatically map {$modelName} to a tokenizer."
        );
    }
}
