<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Rahul900day\Tiktoken\Exceptions\InvalidModelNameException;

class Tiktoken
{
    // [NOTE]: copied from https://github.com/openai/tiktoken/blob/main/tiktoken/model.py needs update regularly.
    private const MODEL_PREFIX_TO_ENCODING = [
        // chat
        'gpt-4-' => 'cl100k_base',
        'gpt-3.5-turbo-' => 'cl100k_base',
        'gpt-35-turbo-' => 'cl100k_base',
        // fine-tuned
        'ft:gpt-4' => 'cl100k_base',
        'ft:gpt-3.5-turbo' => 'cl100k_base',
        'ft:davinci-002' => 'cl100k_base',
        'ft:babbage-002' => 'cl100k_base',
    ];

    private const MODEL_TO_ENCODING = [
        // chat
        'gpt-4' => 'cl100k_base',
        'gpt-3.5-turbo' => 'cl100k_base',
        'gpt-3.5' => 'cl100k_base',
        'gpt-35-turbo' => 'cl100k_base',
        // base
        'davinci-002' => 'cl100k_base',
        'babbage-002' => 'cl100k_base',
        // embeddings
        'text-embedding-ada-002' => 'cl100k_base',
        'text-embedding-3-small' => 'cl100k_base',
        'text-embedding-3-large' => 'cl100k_base',
        // DEPRECATED MODELS
        // text (DEPRECATED)
        'text-davinci-003' => 'p50k_base',
        'text-davinci-002' => 'p50k_base',
        'text-davinci-001' => 'r50k_base',
        'text-curie-001' => 'r50k_base',
        'text-babbage-001' => 'r50k_base',
        'text-ada-001' => 'r50k_base',
        'davinci' => 'r50k_base',
        'curie' => 'r50k_base',
        'babbage' => 'r50k_base',
        'ada' => 'r50k_base',
        // code (DEPRECATED)
        'code-davinci-002' => 'p50k_base',
        'code-davinci-001' => 'p50k_base',
        'code-cushman-002' => 'p50k_base',
        'code-cushman-001' => 'p50k_base',
        'davinci-codex' => 'p50k_base',
        'cushman-codex' => 'p50k_base',
        // edit (DEPRECATED)
        'text-davinci-edit-001' => 'p50k_edit',
        'code-davinci-edit-001' => 'p50k_edit',
        // old embeddings (DEPRECATED)
        'text-similarity-davinci-001' => 'r50k_base',
        'text-similarity-curie-001' => 'r50k_base',
        'text-similarity-babbage-001' => 'r50k_base',
        'text-similarity-ada-001' => 'r50k_base',
        'text-search-davinci-doc-001' => 'r50k_base',
        'text-search-curie-doc-001' => 'r50k_base',
        'text-search-babbage-doc-001' => 'r50k_base',
        'text-search-ada-doc-001' => 'r50k_base',
        'code-search-babbage-code-001' => 'r50k_base',
        'code-search-ada-code-001' => 'r50k_base',
        // open source
        'gpt2' => 'gpt2',
        'gpt-2' => 'gpt2',
    ];

    public static function getEncoding(string $name): Encoder
    {
        return Registry::getEncoding($name);
    }

    public static function getEncodingForModel(string $model): Encoder
    {
        $encodingName = self::getEncodingNameForModel($model);

        return Registry::getEncoding($encodingName);
    }

    protected static function getEncodingNameForModel(string $model): string
    {
        if (array_key_exists($model, self::MODEL_TO_ENCODING)) {
            return self::MODEL_TO_ENCODING[$model];
        }

        foreach (self::MODEL_PREFIX_TO_ENCODING as $prefix => $encodingName) {
            if (str_starts_with($model, $prefix)) {
                return $encodingName;
            }
        }

        throw new InvalidModelNameException($model);
    }
}
