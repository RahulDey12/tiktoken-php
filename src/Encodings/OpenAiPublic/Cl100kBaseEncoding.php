<?php

namespace Rahul900day\Tiktoken\Encodings\OpenAiPublic;

use Rahul900day\Tiktoken\Contracts\Encoding;
use Rahul900day\Tiktoken\Enums\SpecialToken;
use Rahul900day\Tiktoken\Utils\Loader;

final class Cl100kBaseEncoding implements Encoding
{
    protected Loader $loader;

    public function __construct()
    {
        $this->loader = new Loader();
    }

    public function __invoke(): array
    {
        $ranks = $this->loader->loadTiktokenRanks(
            "https://openaipublic.blob.core.windows.net/encodings/cl100k_base.tiktoken",
            "223921b76ee99bde995b7ff738513eef100fb51d18c93597a113bcffe865b2a7",
        );

        return [
            'name' => 'cl100k_base',
            'pat_str' => "/(?i:[sdmt]|ll|ve|re)|[^\r\n\p{L}\p{N}]?+\p{L}+|\p{N}{1,3}| ?[^\s\p{L}\p{N}]++[\r\n]*|\s*[\r\n]|\s+(?!\S)|\s+/u",
            'mergeable_ranks' => $ranks,
            'special_tokens' => [
                SpecialToken::ENDOFTEXT->value => 50256,
                SpecialToken::FIM_PREFIX->value => 50281,
                SpecialToken::FIM_MIDDLE->value => 50282,
                SpecialToken::FIM_SUFFIX->value => 50283,
                SpecialToken::ENDOFPROMPT->value => 100276,
            ],
        ];
    }
}