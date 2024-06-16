<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Exceptions;

class InvalidArraySegmentException extends TiktokenException
{
    public function __construct(int $start, int $end)
    {
        parent::__construct("End index should be greater than start index. Start: {$start}, End: {$end}.");
    }
}
