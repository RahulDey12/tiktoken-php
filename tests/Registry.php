<?php

use Rahul900day\Tiktoken\Encoder;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\Gpt2Encoding;
use Rahul900day\Tiktoken\Exceptions\InvalidEncodingException;
use Rahul900day\Tiktoken\Registry;

it('can give encoding', function (string $name) {
    $encoder = Registry::getEncoding($name);

    expect($encoder)->toBeInstanceOf(Encoder::class);
})->with([
    'gpt2',
    'r50k_base',
    'p50k_base',
    'p50k_edit',
    'cl100k_base',
]);

it('can throw error on wrong encoding', function () {
    Registry::getEncoding('gpt-fake');
})->throws(InvalidEncodingException::class);

it('should not resolve encoding multiple times', function () {
    $encoder1 = Registry::getEncoding('gpt2');
    $encoder2 = Registry::getEncoding('gpt2');

    expect(spl_object_hash($encoder1))->toEqual(spl_object_hash($encoder2));
});

it('can register custom encoding with class', function () {
    $encoding = Mockery::mock(Gpt2Encoding::class);
    $encoder = Mockery::mock(Encoder::class);

    $encoding->shouldReceive('__invoke')
        ->andReturn($encoder)
        ->once();

    $encoder->shouldReceive('encode')->once();

    Registry::registerCustomEncoding('gpt-fake', $encoding);
    Registry::getEncoding('gpt-fake')->encode('Test');
});

it('can register custom encoding with closer', function () {
    $encoder = Mockery::mock(Encoder::class);

    $encoder->shouldReceive('encode')->once();

    Registry::registerCustomEncoding('gpt-fake', function () use ($encoder) {
        return $encoder;
    });

    Registry::getEncoding('gpt-fake')->encode('text');
});

it('can replace new encoding', function () {
    $encoder1 = Mockery::mock(Encoder::class);
    $encoder2 = Mockery::mock(Encoder::class);

    expect(spl_object_hash($encoder1))->not->toBe(spl_object_hash($encoder2));

    Registry::registerCustomEncoding('gpt-fake', fn () => $encoder1);
    expect(spl_object_hash(Registry::getEncoding('gpt-fake')))
        ->toBe(spl_object_hash($encoder1));

    Registry::registerCustomEncoding('gpt-fake', fn () => $encoder2);
    expect(spl_object_hash(Registry::getEncoding('gpt-fake')))
        ->toBe(spl_object_hash($encoder2));
});
