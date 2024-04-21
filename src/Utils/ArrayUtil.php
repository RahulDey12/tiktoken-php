<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Utils;

use Exception;

final class ArrayUtil
{
    public static function &nthItem(array &$array, int $nth): mixed
    {
        $key = array_keys($array)[$nth];

        return $array[$key];
    }

    public static function unsetNthItem(array &$array, int $nth): void
    {
        $key = array_keys($array)[$nth];

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
