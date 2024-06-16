<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Exceptions;

class InvalidChecksumException extends TiktokenException
{
    public function __construct(string $location, string $expectedChecksum)
    {
        parent::__construct(
            "Checksum mismatch for data downloaded from {$location}
            (expected {$expectedChecksum}). This may indicate a corrupted download.
            Please try again."
        );
    }
}
