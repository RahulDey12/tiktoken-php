<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Encodings\OpenAiPublic;

use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Loaders\TiktokenLoader;
use Rahul900day\Tiktoken\Readers\HttpReader;
use Rahul900day\Tiktoken\Vocab;

class Cl100KBaseEncoding extends AbstractEncoding
{
    protected TiktokenLoader $loader;

    public function __construct()
    {
        $this->loader = new TiktokenLoader(HttpReader::create());
    }

    protected function getName(): string
    {
        return 'cl100k_base';
    }

    protected function getPattern(): string
    {
        return "/'(?i:[sdmt]|ll|ve|re)|[^\r\n\p{L}\p{N}]?+\p{L}+|\p{N}{1,3}| ?[^\s\p{L}\p{N}]++[\r\n]*|\s*[\r\n]|\s+(?!\S)|\s+/u";
    }

    protected function getSpecialTokens(): array
    {
        return [
            SpecialToken::ENDOFTEXT->value => 100257,
            SpecialToken::FIM_PREFIX->value => 100258,
            SpecialToken::FIM_MIDDLE->value => 100259,
            SpecialToken::FIM_SUFFIX->value => 100260,
            SpecialToken::ENDOFPROMPT->value => 100276,
        ];
    }

    protected function getVocab(): Vocab
    {
        return new Vocab($this->loader->load(
            'https://openaipublic.blob.core.windows.net/encodings/cl100k_base.tiktoken',
            '223921b76ee99bde995b7ff738513eef100fb51d18c93597a113bcffe865b2a7',
        ));
    }
}
