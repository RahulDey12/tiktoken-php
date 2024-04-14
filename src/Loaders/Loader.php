<?php

namespace Rahul900day\Tiktoken\Loaders;

use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

abstract class Loader
{
    protected Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function readFile(string $blobPath): string
    {
        $client = new Client();
        $res = $client->get($blobPath);

        return $res->getBody()->getContents();
    }

    protected function checkHash(string $data, string $hash): bool
    {
        $actual_hash = hash('sha256', $data);

        return $actual_hash === $hash;
    }

    protected function readFileCached(string $blobPath, ?string $expectedHash = null): string
    {
        $user_specified_cache = true;
        if (getenv("TIKTOKEN_CACHE_DIR")) {
            $cache_dir = Path::canonicalize(getenv("TIKTOKEN_CACHE_DIR"));
        } elseif (getenv("DATA_GYM_CACHE_DIR")) {
            $cache_dir = Path::canonicalize(getenv("DATA_GYM_CACHE_DIR"));
        } else {
            $cache_dir = Path::normalize(sys_get_temp_dir(). '/'. 'data-gym-cache');
            $user_specified_cache = false;
        }

        if ($cache_dir === "") {
            return $this->readFile($blobPath);
        }

        $cache_key = sha1($blobPath);

        $cache_path = Path::canonicalize("{$cache_dir}/{$cache_key}");

        if ($this->filesystem->exists($cache_path)) {
            $data = file_get_contents($cache_path);

            if (is_null($expectedHash) || $this->checkHash($data, $expectedHash)) {
                return $data;
            }

            // the cached file does not match the hash, remove it and re-fetch
            $this->filesystem->remove($cache_path);
        }

        $contents = $this->readFile($blobPath);

        if($expectedHash && ! $this->checkHash($contents, $expectedHash)) {
            throw new \Exception("Hash mismatch for data downloaded from {$blobPath} (expected {$expectedHash}). This may indicate a corrupted download. Please try again.");
        }

        $this->filesystem->mkdir($cache_dir);
        $tmp_filename = $this->filesystem->tempnam($cache_dir, $cache_key, '.tmp');
        file_put_contents($tmp_filename, $contents);
        $this->filesystem->rename($tmp_filename, $cache_path);

        return $contents;
    }
}