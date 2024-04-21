<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Contracts;

interface ReaderContract
{
    public function read(string $location): string;
}
