<?php

use Rahul900day\Tiktoken\Encoder;
use Rahul900day\Tiktoken\Exceptions\InvalidModelNameException;
use Rahul900day\Tiktoken\Tiktoken;

it('can give encoding', function (string $encodingName) {
    $encoding = Tiktoken::getEncoding($encodingName);

    expect($encoding)->toBeInstanceOf(Encoder::class);
})->with([
    'cl100k_base',
    'gpt2'
]);

it('can give encoding from model name', function (string $modelName) {
    $encoding = Tiktoken::getEncodingForModel($modelName);

    expect($encoding)->toBeInstanceOf(Encoder::class);
})->with([
    'gpt-4',
    'gpt-2',
    'text-davinci-003',
    'code-search-ada-code-001'
]);

it('can give encoding from model prefix', function (string $modelName) {
    $encoding = Tiktoken::getEncodingForModel($modelName);

    expect($encoding)->toBeInstanceOf(Encoder::class);
})->with([
    'gpt-4-turbo',
    'ft:gpt-3.5-turbo-xyz',
]);

it('can throw error on wrong model name', function () {
    Tiktoken::getEncodingForModel('gpt-fake');
})->throws(InvalidModelNameException::class);
