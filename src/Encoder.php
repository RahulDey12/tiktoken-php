<?php

namespace Rahul900day\Tiktoken;

use Exception;

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
    ){
        $this->maxTokenValue = max(
            max(array_values($this->vocab->tokenToRanks)),
            max(0, ...array_values($this->specialTokens)),
        );

        if($vocabLength) {
            if(count($this->vocab->tokenToRanks) + count($this->specialTokens) !== $vocabLength) {
                throw new \Exception("Vocab length doesnt match with the actual length of tokens.");
            }

            if($this->maxTokenValue !== $vocabLength - 1) {
                throw new \Exception("Incorrect vocab length.");
            }
        }

        $this->bpe = new Bpe($this->vocab, $this->specialTokens, $this->pattern);
    }

    public function encode(string $text, array|string $allowedSpecial = [], string $disallowedSpecial = 'all'): array
    {
        if($allowedSpecial === 'all') {
            $allowedSpecial = $this->getSpecialTokensKeys();
        }

        if($disallowedSpecial === 'all') {
            $disallowedSpecial = array_diff($this->getSpecialTokensKeys(), $allowedSpecial);
        }

        if(count($disallowedSpecial) > 0) {
            preg_match($this->getSpecialTokenRegex($disallowedSpecial), $text, $matches);

            if(isset($matches[0])) {
                throw new Exception("The text contains a special token that is not allowed: {$matches[0]}");
            }
        }

        return $this->bpe->encode($text, $allowedSpecial)[0];
    }

    protected function getSpecialTokensKeys(): array
    {
        return array_keys($this->specialTokens);
    }

    protected function getSpecialTokenRegex($specialTokens)
    {
        $parts = array_map('preg_quote', $specialTokens);
        $specialRegex = '/'. implode('|', $parts) .'/u';

        if (false === preg_match($specialRegex, null)) {
            throw new Exception("Invalid regex pattern: {$specialRegex}");
        }

        return $specialRegex;
    }
}