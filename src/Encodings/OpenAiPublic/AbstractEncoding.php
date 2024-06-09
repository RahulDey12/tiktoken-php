<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Encodings\OpenAiPublic;

use Rahul900day\Tiktoken\Contracts\EncodingContract;
use Rahul900day\Tiktoken\Encoder;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Vocab;

abstract class AbstractEncoding implements EncodingContract
{
    protected ?int $vocabLength = null;

    abstract protected function getName(): string;

    abstract protected function getPattern(): string;

    /**
     * @return array<string, int>
     */
    abstract protected function getSpecialTokens(): array;

    abstract protected function getVocab(): Vocab;

    public function __invoke(): Encoder
    {
        return new Encoder(
            $this->getName(),
            $this->getPattern(),
            $this->getVocab(),
            $this->getSpecialTokens(),
            $this->vocabLength,
        );
    }

}
