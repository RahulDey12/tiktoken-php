<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Exception;
use Rahul900day\Tiktoken\Exceptions\InvalidPatternException;

class Encoder
{
    protected int $maxTokenValue;

    protected Bpe $bpe;

    public function __construct(
        protected string $name,
        protected string $pattern,
        protected Vocab $vocab,
        protected array $specialTokens,
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
            preg_match($this->getSpecialTokenRegex($disallowedSpecial), $text, $matches);

            if (isset($matches[0])) {
                throw new Exception("The text contains a special token that is not allowed: {$matches[0]}");
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

    protected function getSpecialTokensKeys(): array
    {
        return array_keys($this->specialTokens);
    }

    protected function getSpecialTokenRegex($specialTokens): string
    {
        $parts = array_map('preg_quote', $specialTokens);
        $specialRegex = '/'.implode('|', $parts).'/u';

        if (@preg_match($specialRegex, '') === false) {
            throw new InvalidPatternException($specialRegex);
        }

        return $specialRegex;
    }
}
