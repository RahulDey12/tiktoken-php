<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Utils;

final class EncoderUtil
{
    /**
     * @return array<int>
     */
    public static function toBytes(string $string): array
    {
        return array_values(unpack('C*', mb_convert_encoding($string, 'UTF-8')) ?: []);
    }

    /**
     * @param  array<int>  $bytes
     */
    public static function fromBytes(array $bytes): string
    {
        return pack('C*', ...$bytes);
    }
}
