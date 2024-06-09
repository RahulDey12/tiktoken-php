<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Encodings\OpenAiPublic;

use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Loaders\TiktokenLoader;
use Rahul900day\Tiktoken\Readers\HttpReader;
use Rahul900day\Tiktoken\Vocab;

class R50KBaseEncoding extends AbstractEncoding
{
    protected TiktokenLoader $loader;

    protected ?int $vocabLength = 50257;

    public function __construct()
    {
        $this->loader = new TiktokenLoader(HttpReader::create());
    }

    protected function getName(): string
    {
        return 'r50k_base';
    }

    protected function getPattern(): string
    {
        return "/'(?:[sdmt]|ll|ve|re)| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+/u";
    }

    /**
     * {@inheritDoc}
     */
    protected function getSpecialTokens(): array
    {
        return [
            SpecialToken::ENDOFTEXT->value => 50256,
        ];
    }

    protected function getVocab(): Vocab
    {
        return new Vocab($this->loader->load(
            'https://openaipublic.blob.core.windows.net/encodings/r50k_base.tiktoken',
            '306cd27f03c1a714eca7108e03d66b7dc042abe8c258b44c199a7ed9838dd930',
        ));
    }
}
