<?php

use Rahul900day\Tiktoken\Utils\EncoderUtil;

it('can encode string', function () {
    $bytes = EncoderUtil::toBytes('$Test 😄 1234');

    expect($bytes)->toMatchSnapshot();
});

it('can decode bytes', function () {
    $string = EncoderUtil::fromBytes([200, 30, 26, 48, 96, 36]);

    expect($string)->toMatchSnapshot();
});
