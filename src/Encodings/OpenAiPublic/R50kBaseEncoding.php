<?php

namespace Rahul900day\Tiktoken\Encodings\OpenAiPublic;

use Rahul900day\Tiktoken\Contracts\Encoding;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Utils\Loader;

final class R50kBaseEncoding implements Encoding
{
    protected Loader $loader;

    public function __construct()
    {
        $this->loader = new Loader();
    }

    public function __invoke(): array
    {
        $ranks = $this->loader->loadTiktokenRanks(
            "https://openaipublic.blob.core.windows.net/encodings/r50k_base.tiktoken",
            "306cd27f03c1a714eca7108e03d66b7dc042abe8c258b44c199a7ed9838dd930",
        );

        return [
            'name' => 'r50k_base',
            'explicit_n_vocab' => 50257,
            'pat_str' => "/'(?:[sdmt]|ll|ve|re)| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+/u",
            'mergeable_ranks' => $ranks,
            'special_tokens' => [
                SpecialToken::ENDOFTEXT->value => 50256,
            ],
        ];
    }
}