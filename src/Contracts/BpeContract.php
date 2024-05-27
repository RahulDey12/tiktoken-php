<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Contracts;

interface BpeContract
{
    public function encode(string $text, array $allowedSpecial): array;

    public function encodeOrdinary(string $text): array;
}
