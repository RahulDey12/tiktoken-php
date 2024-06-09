<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Loaders;

final class TiktokenLoader extends Loader
{
    /**
     * @return non-empty-array<int|string, int>
     *
     * @throws \Rahul900day\Tiktoken\Exceptions\InvalidChecksumException
     */
    public function load(string $bpeFile, ?string $expectedHash = null): array
    {
        $contents = $this->readFileCached($bpeFile, $expectedHash);
        $result = [];

        foreach (explode("\n", $contents) as $line) {
            if ($line === '') {
                continue;
            }

            [$token, $rank] = explode(' ', $line);
            $result[base64_decode($token)] = intval($rank);
        }

        if (count($result) === 0) {
            throw new \Exception('Invalid tiktoken');
        }

        return $result;
    }
}
