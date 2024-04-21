<?php

namespace Rahul900day\Tiktoken\Exceptions;

use Exception;

class InvalidChecksumException extends Exception
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
