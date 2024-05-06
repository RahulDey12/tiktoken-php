<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Cache;

use Rahul900day\Tiktoken\Contracts\CacheContract;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter as CacheAdapter;
use Symfony\Component\Filesystem\Path;

class FileSystemCache implements CacheContract
{
    protected CacheAdapter $cache;

    protected string $directory;

    public function __construct(?string $directory = null)
    {
        $this->cache = new CacheAdapter(
            'tiktoken-cache',
            0,
            $directory ?? $this->getDefaultCacheDirectory(),
        );
    }

    public function get(string $key): mixed
    {
        return $this->cache->getItem($key)->get();
    }

    public function set(string $key, mixed $value): void
    {
        $item = $this->cache->getItem($key);
        $item->set($value);

        $this->cache->save($item);
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function has(string $key): bool
    {
        return $this->cache->hasItem($key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    protected function getDefaultCacheDirectory(): string
    {
        if (getenv('TIKTOKEN_CACHE_DIR')) {
            return Path::canonicalize(getenv('TIKTOKEN_CACHE_DIR'));
        }
        if (getenv('DATA_GYM_CACHE_DIR')) {
            return Path::canonicalize(getenv('DATA_GYM_CACHE_DIR'));
        }

        return Path::normalize(sys_get_temp_dir());
    }
}
