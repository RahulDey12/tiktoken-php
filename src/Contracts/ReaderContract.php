<?php

namespace Rahul900day\Tiktoken\Contracts;

interface ReaderContract
{
    public function read(string $location): string;
}