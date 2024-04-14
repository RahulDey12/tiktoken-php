<?php

namespace Rahul900day\Tiktoken\Contracts;

use Rahul900day\Tiktoken\Encoder;

interface EncodingContract
{
    public function __invoke(): Encoder;
}