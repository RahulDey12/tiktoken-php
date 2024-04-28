<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Utils;

use Exception;

final class ArrayUtil
{
    public static function &at(array &$array, int $at): mixed
    {
        $key = array_keys($array)[$at];

        return $array[$key];
    }

    public static function unsetAt(array &$array, int $at): void
    {
        $key = array_keys($array)[$at];

        unset($array[$key]);
    }

    public static function getSegment(array $array, int $start, int $end): array
    {
        if ($end <= $start) {
            throw new Exception("End index should be greater than start index. Start: {$start}, End: {$end}.");
        }

        return array_slice($array, $start, $end - $start);
    }
}
