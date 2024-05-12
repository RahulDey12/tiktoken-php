<?php

use Rahul900day\Tiktoken\Tiktoken;

test('simple', function () {
    $encoding = Tiktoken::getEncoding('gpt2');

    expect($encoding->encode("hello world"))->toBe([31373, 995])
        ->and($encoding->decode([31373, 995]))->toBe('hello world')
        ->and($encoding->encode("hello <|endoftext|>", allowedSpecial: 'all'))->toBe([31373, 220, 50256]);

    $encoding = Tiktoken::getEncoding('cl100k_base');

    expect($encoding->encode("hello world"))->toBe([15339, 1917])
        ->and($encoding->decode([15339, 1917]))->toBe('hello world')
        ->and($encoding->encode("hello <|endoftext|>", allowedSpecial: 'all'))->toBe([15339, 220, 100257]);
});

test('simple repeated', function () {
    $encoding = Tiktoken::getEncoding('gpt2');

    expect($encoding->encode("0"))->toBe([15])
        ->and($encoding->encode("00"))->toBe([405])
        ->and($encoding->encode("000"))->toBe([830])
        ->and($encoding->encode("0000"))->toBe([2388])
        ->and($encoding->encode("00000"))->toBe([20483])
        ->and($encoding->encode("000000"))->toBe([10535])
        ->and($encoding->encode("0000000"))->toBe([24598])
        ->and($encoding->encode("00000000"))->toBe([8269])
        ->and($encoding->encode("000000000"))->toBe([10535, 830])
        ->and($encoding->encode("0000000000"))->toBe([8269, 405])
        ->and($encoding->encode("00000000000"))->toBe([8269, 830])
        ->and($encoding->encode("000000000000"))->toBe([8269, 2388])
        ->and($encoding->encode("0000000000000"))->toBe([8269, 20483])
        ->and($encoding->encode("00000000000000"))->toBe([8269, 10535])
        ->and($encoding->encode("000000000000000"))->toBe([8269, 24598])
        ->and($encoding->encode("0000000000000000"))->toBe([25645])
        ->and($encoding->encode("00000000000000000"))->toBe([8269, 10535, 830]);
});

test('simple regex', function () {
    $encoding = Tiktoken::getEncoding('cl100k_base');

    expect($encoding->encode("rer"))->toBe([38149])
        ->and($encoding->encode("'rer"))->toBe([2351, 81])
        ->and($encoding->encode("today\n "))->toBe([31213, 198, 220])
        ->and($encoding->encode("today\n \n"))->toBe([31213, 27907])
        ->and($encoding->encode("today\n  \n"))->toBe([31213, 14211]);
});

test('basic encode', function () {
    $encoding = Tiktoken::getEncoding('r50k_base');
    expect($encoding->encode("hello world"))->toBe([31373, 995]);

    $encoding = Tiktoken::getEncoding('p50k_base');
    expect($encoding->encode("hello world"))->toBe([31373, 995]);

    $encoding = Tiktoken::getEncoding('cl100k_base');
    expect($encoding->encode("hello world"))->toBe([15339, 1917]);

//    ->and($encoding->encode(' \x850'))->toBe([220, 126, 227, 15])
});

test('encode empty', function () {
    $encoding = Tiktoken::getEncoding('r50k_base');
    expect($encoding->encode(""))->toBe([]);
});

test('basic round-trip', function (string $encodingName) {
    $encoding = Tiktoken::getEncoding($encodingName);

    $values = [
        "hello",
        "hello ",
        "hello  ",
        " hello",
        " hello ",
        " hello  ",
        "hello world",
        "请考试我的软件！12345",
    ];

    foreach ($values as $value) {
        expect($encoding->decode($encoding->encode($value)))->toBe($value)
            ->and($encoding->decode($encoding->encodeOrdinary($value)))->toBe($value);
    }

})->with([
    'r50k_base',
    'cl100k_base',
]);
