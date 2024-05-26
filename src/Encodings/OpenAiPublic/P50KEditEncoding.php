<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Encodings\OpenAiPublic;

use Rahul900day\Tiktoken\Contracts\EncodingContract;
use Rahul900day\Tiktoken\Encoder;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Loaders\TiktokenLoader;
use Rahul900day\Tiktoken\Readers\HttpReader;
use Rahul900day\Tiktoken\Vocab;

class P50KEditEncoding extends AbstractEncoding
{
    protected TiktokenLoader $loader;

    public function __construct()
    {
        $this->loader = new TiktokenLoader(HttpReader::create());
    }

    protected function getName(): string
    {
        return 'p50k_base';
    }

    protected function getPattern(): string
    {
        return "/'(?:[sdmt]|ll|ve|re)| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+/u";
    }

    protected function getSpecialTokens(): array
    {
        return [
            SpecialToken::ENDOFTEXT->value => 50256,
            SpecialToken::FIM_PREFIX->value => 50281,
            SpecialToken::FIM_MIDDLE->value => 50282,
            SpecialToken::FIM_SUFFIX->value => 50283,
        ];
    }

    protected function getVocab(): Vocab
    {
        return new Vocab($this->loader->load(
            'https://openaipublic.blob.core.windows.net/encodings/p50k_base.tiktoken',
            '94b5ca7dff4d00767bc256fdd1b27e5b17361d7b8a5f968547f9f23eb70d2069',
        ));
    }
}
