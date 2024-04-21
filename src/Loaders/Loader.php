<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Loaders;

use Rahul900day\Tiktoken\Cache\FileSystemCache;
use Rahul900day\Tiktoken\Contracts\CacheContract;
use Rahul900day\Tiktoken\Contracts\ReaderContract;

abstract class Loader
{
    protected CacheContract $cache;

    public function __construct(protected ReaderContract $reader, ?CacheContract $cache = null)
    {
        $this->cache = $cache ?? new FileSystemCache();
    }

    protected function checkHash(string $data, string $hash): bool
    {
        $actual_hash = hash('sha256', $data);

        return $actual_hash === $hash;
    }

    protected function readFileCached(string $location, ?string $expectedHash = null): string
    {
        // TODO: Add skip cache option

        $cacheKey = sha1($location);

        if ($this->cache->has($cacheKey)) {
            $data = $this->cache->get($cacheKey);

            if (is_null($expectedHash) || $this->checkHash($data, $expectedHash)) {
                return $data;
            }

            $this->cache->delete($cacheKey);
        }

        $contents = $this->reader->read($location);

        if ($expectedHash && ! $this->checkHash($contents, $expectedHash)) {
            throw new \Exception("Hash mismatch for data downloaded from {$location} (expected {$expectedHash}). This may indicate a corrupted download. Please try again.");
        }

        $this->cache->set($cacheKey, $contents);

        return $contents;
    }
}
