<?php

use Rahul900day\Tiktoken\Contracts\BpeContract;
use Rahul900day\Tiktoken\Encoder;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Exceptions\SpecialTokenNotAllowedException;
use Rahul900day\Tiktoken\Vocab;

beforeEach(function () {
    $this->vocab = new Vocab([
        'a' => 1,
        'b' => 2,
        'c' => 3,
        'd' => 4,
        'e' => 5,
        'f' => 6,
        ' ' => 7,
        0 => 8,
        1 => 9,
        2 => 10,
    ]);

    $this->bpe = Mockery::mock(BpeContract::class);
});

it('can encode a text', function () {
    $this->bpe->shouldReceive('encode')
        ->withArgs(['Fake', []])
        ->andReturn([[1, 2, 3], 0])
        ->once();

    $encoder = new Encoder('gpt-fake', '',  $this->vocab, [], 10, $this->bpe);

    expect($encoder->encode('Fake'))->toBe([1, 2, 3]);
});

it('can decode ranks', function () {
    $encoder = new Encoder('gpt-fake', '',  $this->vocab, [], 10, $this->bpe);

    expect($encoder->decode([1, 2, 3]))->toBe('abc');
});

it('can throw error on special token', function () {
    $encoder = new Encoder(
        'gpt-fake',
        '\s',
        $this->vocab,
        [SpecialToken::ENDOFTEXT->value => 5],
        bpe: $this->bpe
    );

    $encoder->encode('Fake <|endoftext|>');
})->throws(SpecialTokenNotAllowedException::class);

it('cannot throw error when special token allowed', function () {
    $this->bpe->shouldReceive('encode')->andReturn([[1, 2, 3], 0]);

    $encoder = new Encoder(
        'gpt-fake',
        '\s',
        $this->vocab,
        [SpecialToken::ENDOFTEXT->value => 5, SpecialToken::STARTOFTEXT->value => 4],
        bpe: $this->bpe
    );

    $encoder->encode('Fake <|endoftext|>', [SpecialToken::ENDOFTEXT->value]);
})->throwsNoExceptions();

it('cannot throw error when all special tokens are allowed', function () {
    $this->bpe->shouldReceive('encode')->andReturn([[1, 2, 3], 0]);

    $encoder = new Encoder(
        'gpt-fake',
        '\s',
        $this->vocab,
        [SpecialToken::ENDOFTEXT->value => 5, SpecialToken::STARTOFTEXT->value => 4],
        bpe: $this->bpe
    );

    $encoder->encode('<|startoftext|> Fake <|endoftext|>', 'all');
})->throwsNoExceptions();

it('can encode batch', function () {
    $this->bpe->shouldReceive('encode')
        ->andReturn([[1, 2, 3], 0], [[4, 5, 6], 0])
        ->twice();

    $encoder = new Encoder('gpt-fake', '',  $this->vocab, [], 10, $this->bpe);

    expect($encoder->encodeBatch(['Fake', 'Fake1']))->toBe([[1, 2, 3], [4, 5, 6]]);
});

it('can decode batch', function () {
    $encoder = new Encoder('gpt-fake', '',  $this->vocab, [], 10, $this->bpe);

    expect($encoder->decodeBatch([[1, 2, 3], [4, 5, 6]]))->toBe(['abc', 'def']);
});

it('can encode ordinary', function () {
    $this->bpe->shouldReceive('encodeOrdinary')
        ->andReturn([1, 2, 3])
        ->once();

    $encoder = new Encoder('gpt-fake', '',  $this->vocab, [], 10, $this->bpe);

    expect($encoder->encodeOrdinary('Fake'))->toBe([1, 2, 3]);
});

it('can encode ordinary batch', function () {
    $this->bpe->shouldReceive('encodeOrdinary')
        ->andReturn([1, 2, 3], [4, 5, 6])
        ->twice();

    $encoder = new Encoder('gpt-fake', '',  $this->vocab, [], 10, $this->bpe);

    expect($encoder->encodeOrdinaryBatch(['Fake', 'Fake1']))->toBe([[1, 2, 3], [4, 5, 6]]);
});
