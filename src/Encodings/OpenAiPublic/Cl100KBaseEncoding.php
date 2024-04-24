<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Encodings\OpenAiPublic;

use Rahul900day\Tiktoken\Contracts\EncodingContract;
use Rahul900day\Tiktoken\Encoder;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Loaders\TiktokenLoader;
use Rahul900day\Tiktoken\Readers\HttpReader;
use Rahul900day\Tiktoken\Vocab;

class Cl100KBaseEncoding implements EncodingContract
{
    protected TiktokenLoader $loader;

    public function __construct()
    {
        $this->loader = new TiktokenLoader(HttpReader::create());
    }

    public function __invoke(): Encoder
    {
        $vocab = new Vocab($this->loader->load(
            'https://openaipublic.blob.core.windows.net/encodings/cl100k_base.tiktoken',
            '223921b76ee99bde995b7ff738513eef100fb51d18c93597a113bcffe865b2a7',
        ));

        return new Encoder(
            'cl100k_base',
            "/'(?i:[sdmt]|ll|ve|re)|[^\r\n\p{L}\p{N}]?+\p{L}+|\p{N}{1,3}| ?[^\s\p{L}\p{N}]++[\r\n]*|\s*[\r\n]|\s+(?!\S)|\s+/u",
            $vocab,
            [
                SpecialToken::ENDOFTEXT->value => 50256,
                SpecialToken::FIM_PREFIX->value => 50281,
                SpecialToken::FIM_MIDDLE->value => 50282,
                SpecialToken::FIM_SUFFIX->value => 50283,
                SpecialToken::ENDOFPROMPT->value => 100276,
            ]
        );
    }
}
