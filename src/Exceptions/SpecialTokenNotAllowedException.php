<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Exceptions;

class SpecialTokenNotAllowedException extends TiktokenException
{
    public function __construct(string $specialToken)
    {
        parent::__construct(
            "The text contains a special token that is not allowed: {$specialToken}",
        );
    }
}
