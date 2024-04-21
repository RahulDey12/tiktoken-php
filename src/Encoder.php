<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Exceptions\SpecialTokenNotAllowedException;

final class Encoder
{
    private int $maxTokenValue;

    private Bpe $bpe;

    public function __construct(
        public readonly string $name,
        private readonly string $pattern,
        private readonly Vocab $vocab,
        private readonly array $specialTokens,
        ?int $vocabLength = null,
    ) {
        $this->maxTokenValue = max(
            max(array_values($this->vocab->tokenToRanks)),
            max(0, ...array_values($this->specialTokens)),
        );

        if ($vocabLength) {
            if (count($this->vocab->tokenToRanks) + count($this->specialTokens) !== $vocabLength) {
                throw new \Exception('Vocab length doesnt match with the actual length of tokens.');
            }

            if ($this->maxTokenValue !== $vocabLength - 1) {
                throw new \Exception('Incorrect vocab length.');
            }
        }

        $this->bpe = new Bpe($this->vocab, $this->specialTokens, $this->pattern);
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
            preg_match(SpecialToken::getRegex($disallowedSpecial), $text, $matches);

            if (isset($matches[0])) {
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

    private function getSpecialTokensKeys(): array
    {
        return array_keys($this->specialTokens);
    }
}
