<?php

namespace Rahul900day\Tiktoken\Encodings\OpenAiPublic;

use Rahul900day\Tiktoken\Contracts\EncodingContract;
use Rahul900day\Tiktoken\Encoder;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Loaders\TiktokenLoader;
use Rahul900day\Tiktoken\Readers\HttpReader;
use Rahul900day\Tiktoken\Vocab;

final class P50KBaseEncoding implements EncodingContract
{
    protected TiktokenLoader $loader;

    public function __construct()
    {
        $this->loader = new TiktokenLoader(HttpReader::create());
    }

    public function __invoke(): Encoder
    {
        $vocab = new Vocab($this->loader->load(
            "https://openaipublic.blob.core.windows.net/encodings/p50k_base.tiktoken",
            "94b5ca7dff4d00767bc256fdd1b27e5b17361d7b8a5f968547f9f23eb70d2069",
        ));

        return new Encoder(
            'p50k_base',
            "/'(?:[sdmt]|ll|ve|re)| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+/u",
            $vocab,
            [
                SpecialToken::ENDOFTEXT->value => 50256,
            ],
            50281,
        );
    }
}