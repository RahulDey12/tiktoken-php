<?php

namespace Rahul900day\Tiktoken\Contracts;

interface Encoding
{
    public function __invoke(): array;
}