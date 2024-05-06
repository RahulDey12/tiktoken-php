<?php

use Rahul900day\Tiktoken\Exceptions\InvalidArraySegmentException;
use Rahul900day\Tiktoken\Utils\ArrayUtil;

beforeEach(function () {
    $this->array = [
        0 => 1,
        2 => 5,
        6 => 8,
        1 => 5,
        3 => 7,
        5 => 9,
    ];
});

it('can return nth element', function (mixed $at, mixed $value) {
    expect(ArrayUtil::at($this->array, $at))->toBe($value);
})->with([
    [0, 1],
    [3, 5],
    [1, 5],
]);

it('can unset nth element', function () {
    ArrayUtil::unsetAt($this->array, 3);

    expect(ArrayUtil::at($this->array, 3))->toBe(7);
});

it('can give segment', function (int $start, int $end, array $expected) {
    expect(ArrayUtil::getSegment($this->array, $start, $end))->toBe($expected);
})->with([
    [0, 2, [1, 5]],
    [2, 5, [8, 5, 7]],
]);

it('can throw error while give invalid segment', function () {
    ArrayUtil::getSegment([1, 2, 3, 4, 5, 6], 6, 3);
})->throws(InvalidArraySegmentException::class);
