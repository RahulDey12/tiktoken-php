<?php

namespace Rahul900day\Tiktoken\Contracts;

use Rahul900day\Tiktoken\Encoder;

interface Encoding
{
    public function __invoke(): Encoder;
}