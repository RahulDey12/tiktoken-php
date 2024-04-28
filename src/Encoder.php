<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Rahul900day\Tiktoken\Contracts\BpeContract;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Exceptions\SpecialTokenNotAllowedException;

class Encoder
{
    protected int $maxTokenValue;

    public function __construct(
        public readonly string $name,
        protected readonly string $pattern,
        protected readonly Vocab $vocab,
        protected readonly array $specialTokens,
        protected ?int $vocabLength = null,
        protected ?BpeContract $bpe = null,
    ) {
        if(! $this->bpe) {
            $this->initializeBpe();
        }
    }

    public function encodeOrdinary(string $text): array
    {
        return $this->bpe->encodeOrdinary($text);
    }

    public function encodeOrdinaryBatch(array $texts): array
    {
        $result = [];

        foreach ($texts as $text) {
            $result[] = $this->encodeOrdinary($text);
        }

        return $result;
    }

    public function encode(string $text, array|string $allowedSpecial = [], string $disallowedSpecial = 'all'): array
    {
        if ($allowedSpecial === 'all') {
            $allowedSpecial = $this->getSpecialTokensKeys();
        }

        if ($disallowedSpecial === 'all') {
            $disallowedSpecial = array_diff($this->getSpecialTokensKeys(), $allowedSpecial);
        }

        if (count($disallowedSpecial) > 0) {
            $regex = SpecialToken::getRegex($disallowedSpecial);

            if (preg_match($regex, $text, $matches)) {
                throw new SpecialTokenNotAllowedException($matches[0]);
            }
        }

        return $this->bpe->encode($text, $allowedSpecial)[0];
    }

    public function encodeBatch(array $texts, array|string $allowedSpecial = [], string $disallowedSpecial = 'all'): array
    {
        $result = [];

        foreach ($texts as $text) {
            $result[] = $this->encode($text, $allowedSpecial, $disallowedSpecial);
        }

        return $result;
    }

    public function decode(array $tokens): string
    {
        $text = '';

        foreach ($tokens as $token) {
            $text .= $this->vocab->getToken($token);
        }

        return $text;
    }

    public function decodeBatch(array $batch): array
    {
        $texts = [];

        foreach ($batch as $tokens) {
            $texts[] = $this->decode($tokens);
        }

        return $texts;
    }

    protected function initializeBpe(): void
    {
        $this->maxTokenValue = max(
            max(array_values($this->vocab->tokenToRanks)),
            max(0, ...array_values($this->specialTokens)),
        );

        $this->validateBpe();

        $this->setBpe(new Bpe($this->vocab, $this->specialTokens, $this->pattern));
    }

    protected function validateBpe(): void
    {
        if ($this->vocabLength) {
            if (count($this->vocab->tokenToRanks) + count($this->specialTokens) !== $this->vocabLength) {
                throw new \Exception('Vocab length doesnt match with the actual length of tokens.');
            }

            if ($this->maxTokenValue !== $this->vocabLength - 1) {
                throw new \Exception('Incorrect vocab length.');
            }
        }
    }

    public function setBpe(Bpe $bpe): void
    {
        $this->bpe = $bpe;
    }

    protected function getSpecialTokensKeys(): array
    {
        return array_keys($this->specialTokens);
    }
}
