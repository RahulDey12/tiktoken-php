<?php

use Rahul900day\Tiktoken\Exceptions\RankNotFoundException;
use Rahul900day\Tiktoken\Vocab;

beforeEach(function () {
    $this->vocab = new Vocab([
        'a' => 0,
        'b' => 1,
        'c' => 2,
        ' ' => 3,
        0 => 40,
        1 => 50,
        2 => 60,
    ]);
});

it('can give rank', function (string|int $token, ?int $rank) {
    expect($this->vocab->getRank($token))->toBe($rank);
})->with([
    ['a', 0],
    ['c', 2],
    [' ', 3],
    [2, 60],
    ['x', null],
]);

it('can give token', function (string $token, int $rank) {
    expect($this->vocab->getToken($rank))->toBe($token);
})->with([
    ['a', 0],
    [' ', 3],
    ['2', 60],
    ['0', 40],
]);

it('can throw error when rank not found', function () {
    $this->vocab->getToken(5000);
})->throws(RankNotFoundException::class);

it('can count tokens', function () {
    expect($this->vocab->count())->toBe(7);
});
