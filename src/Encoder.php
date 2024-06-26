<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Rahul900day\Tiktoken\Contracts\BpeContract;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Exceptions\InvalidPatternException;
use Rahul900day\Tiktoken\Exceptions\RankNotFoundException;
use Rahul900day\Tiktoken\Exceptions\SpecialTokenNotAllowedException;
use Rahul900day\Tiktoken\Exceptions\TiktokenException;

class Encoder
{
    protected int $maxTokenValue;

    /**
     * @param  array<string, int>  $specialTokens
     */
    public function __construct(
        public readonly string $name,
        public readonly string $pattern,
        protected readonly Vocab $vocab,
        public readonly array $specialTokens,
        public readonly ?int $vocabLength = null,
        protected ?BpeContract $bpe = null,
    ) {
        if (is_null($this->bpe)) {
            $this->initializeBpe();
        }
    }

    /**
     * @return int[]
     *
     * @throws TiktokenException
     */
    public function encodeOrdinary(string $text): array
    {
        return $this->getBpe()->encodeOrdinary($text);
    }

    /**
     * @param  string[]  $texts
     * @return array<int[]>
     *
     * @throws TiktokenException
     */
    public function encodeOrdinaryBatch(array $texts): array
    {
        $result = [];

        foreach ($texts as $text) {
            $result[] = $this->encodeOrdinary($text);
        }

        return $result;
    }

    /**
     * @param  string[]|'all'  $allowedSpecial
     * @param  string[]|'all'  $disallowedSpecial
     * @return int[]
     *
     * @throws SpecialTokenNotAllowedException|InvalidPatternException|TiktokenException
     */
    public function encode(string $text, array|string $allowedSpecial = [], string|array $disallowedSpecial = 'all'): array
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

        return $this->getBpe()->encode($text, $allowedSpecial)[0];
    }

    /**
     * @param  array<string>  $texts
     * @param  string[]|'all'  $allowedSpecial
     * @param  string[]|'all'  $disallowedSpecial
     * @return array<int[]>
     *
     * @throws SpecialTokenNotAllowedException|InvalidPatternException|TiktokenException
     */
    public function encodeBatch(array $texts, array|string $allowedSpecial = [], string|array $disallowedSpecial = 'all'): array
    {
        $result = [];

        foreach ($texts as $text) {
            $result[] = $this->encode($text, $allowedSpecial, $disallowedSpecial);
        }

        return $result;
    }

    /**
     * @param  int[]  $tokens
     *
     * @throws RankNotFoundException
     */
    public function decode(array $tokens): string
    {
        $text = '';

        foreach ($tokens as $token) {
            try {
                $text .= $this->vocab->getToken($token);
            } catch (RankNotFoundException $exception) {
                $piece = array_search($token, $this->specialTokens);

                if (! $piece) {
                    throw $exception;
                }

                $text .= $piece;
            }
        }

        return $text;
    }

    /**
     * @param  array<int[]>  $batch
     * @return array<string>
     *
     * @throws RankNotFoundException
     */
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
                throw new TiktokenException('Vocab length doesnt match with the actual length of tokens.');
            }

            if ($this->maxTokenValue !== $this->vocabLength - 1) {
                throw new TiktokenException('Incorrect vocab length.');
            }
        }
    }

    public function setBpe(Bpe $bpe): void
    {
        $this->bpe = $bpe;
    }

    public function getBpe(): BpeContract
    {
        if (is_null($this->bpe)) {
            throw new TiktokenException('Bpe Not Found');
        }

        return $this->bpe;
    }

    /**
     * @return array<string>
     */
    protected function getSpecialTokensKeys(): array
    {
        return array_keys($this->specialTokens);
    }
}
