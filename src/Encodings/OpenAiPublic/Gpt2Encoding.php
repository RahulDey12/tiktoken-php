<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Encodings\OpenAiPublic;

use Rahul900day\Tiktoken\Contracts\EncodingContract;
use Rahul900day\Tiktoken\Encoder;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Loaders\DataGymLoader;
use Rahul900day\Tiktoken\Readers\HttpReader;
use Rahul900day\Tiktoken\Vocab;

final class Gpt2Encoding implements EncodingContract
{
    private DataGymLoader $loader;

    public function __construct()
    {
        $this->loader = new DataGymLoader(HttpReader::create());
    }

    public function __invoke(): Encoder
    {
        $vocab = new Vocab($this->loader->load(
            'https://openaipublic.blob.core.windows.net/gpt-2/encodings/main/vocab.bpe',
            'https://openaipublic.blob.core.windows.net/gpt-2/encodings/main/encoder.json',
            '1ce1664773c50f3e0cc8842619a93edc4624525b728b188a9e0be33b7726adc5',
            '196139668be63f3b5d6574427317ae82f612a97c5d1cdaf36ed2256dbf636783',
        ));

        return new Encoder(
            'gpt2',
            "/'(?:[sdmt]|ll|ve|re)| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+/u",
            $vocab,
            [
                SpecialToken::ENDOFTEXT->value => 50256,
            ],
            50257,
        );
    }
}
