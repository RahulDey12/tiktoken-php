<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Contracts;

interface BpeContract
{
    /**
     * @param string $text
     * @param string[] $allowedSpecial
     * @return array{0: int[], 1, int}
     */
    public function encode(string $text, array $allowedSpecial): array;

    /**
     * @param string $text
     * @return int[]
     */
    public function encodeOrdinary(string $text): array;
}
