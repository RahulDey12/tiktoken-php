<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Closure;
use Exception;
use Rahul900day\Tiktoken\Contracts\Encoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\Cl100kBaseEncoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\Gpt2Encoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\P50kBaseEncoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\P50kEditEncoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\R50kBaseEncoding;

class Registry
{
    protected static array $resolvedEncodings = [];

    protected static array $encodings = [];

    public static function registerEncoding(string $name, Encoding|Closure $encoding): void
    {
        self::$encodings[$name] = $encoding;
    }

    public function getEncoding(string $name): Encoder
    {
        if(array_key_exists($name, self::$resolvedEncodings)) {
            return self::$resolvedEncodings[$name];
        }

        if(count(self::$encodings) === 0) {
            $this->loadDefaultEncodings();
        }

        if(! array_key_exists($name, self::$encodings)) {
            throw new Exception("Unknown encoding {$name}.");
        }

        $callable = self::$encodings[$name];
        $encoding = $callable();
        self::$resolvedEncodings[$name] = $encoding;

        return $encoding;
    }

    protected function loadDefaultEncodings()
    {
        self::registerEncoding('gpt2', new Gpt2Encoding());
        self::registerEncoding('r50k_base', new R50kBaseEncoding());
        self::registerEncoding('p50k_base', new P50kBaseEncoding());
        self::registerEncoding('p50k_edit', new P50kEditEncoding());
        self::registerEncoding('cl100k_base', new Cl100kBaseEncoding());
    }
}