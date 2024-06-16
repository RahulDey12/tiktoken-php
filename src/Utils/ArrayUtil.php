<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Utils;

use Rahul900day\Tiktoken\Exceptions\InvalidArraySegmentException;

final class ArrayUtil
{
    /**
     * @template TKey
     * @template TValue
     *
     * @param  array<TKey, TValue>  $array
     * @return TValue
     */
    public static function &at(array &$array, int $at): mixed
    {
        $key = array_keys($array)[$at];

        return $array[$key];
    }

    /**
     * @template TKey
     * @template TValue
     *
     * @param  array<TKey, TValue>  $array
     */
    public static function unsetAt(array &$array, int $at): void
    {
        $key = array_keys($array)[$at];

        unset($array[$key]);
    }

    /**
     * @template TKey
     * @template TValue
     *
     * @param  array<TKey, TValue>  $array
     * @return array<TKey, TValue>
     */
    public static function getSegment(array $array, int $start, int $end): array
    {
        if ($end <= $start) {
            throw new InvalidArraySegmentException($start, $end);
        }

        return array_slice($array, $start, $end - $start);
    }
}
