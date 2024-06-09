<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Contracts;

interface BpeContract
{
    /**
     * @param  string[]  $allowedSpecial
     * @return array{0: int[], 1, int}
     */
    public function encode(string $text, array $allowedSpecial): array;

    /**
     * @return int[]
     */
    public function encodeOrdinary(string $text): array;
}
