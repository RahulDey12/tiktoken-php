<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Contracts;

interface CacheContract
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value): void;

    public function delete(string $key): bool;

    public function has(string $key): bool;

    public function clear(): bool;
}
