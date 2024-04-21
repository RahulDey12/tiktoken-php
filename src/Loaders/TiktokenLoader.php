<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Loaders;

final class TiktokenLoader extends Loader
{
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

        return $result;
    }
}
