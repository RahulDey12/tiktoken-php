<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Closure;
use Exception;
use Rahul900day\Tiktoken\Contracts\EncodingContract;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\Cl100KBaseEncoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\Gpt2Encoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\P50KBaseEncoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\P50KEditEncoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\R50KBaseEncoding;

class Registry
{
    protected static array $resolvedEncodings = [];

    protected static array $encodings = [];

    public static array $defaultEncodings = [
        'gpt2' => [Gpt2Encoding::class, []],
        'r50k_base' => [R50KBaseEncoding::class, []],
        'p50k_base' => [P50KBaseEncoding::class, []],
        'p50k_edit' => [P50KEditEncoding::class, []],
        'cl100k_base' => [Cl100KBaseEncoding::class, []],
    ];

    protected static function registerEncoding(string $name, EncodingContract|Closure $encoding): void
    {
        self::$encodings[$name] = $encoding;
    }

    public static function registerCustomEncoding(string $name, EncodingContract|Closure $encoding): void
    {
        // Register default encodings before register any custom
        // encoding. This is how we can add replacement capabilities.
        if(count(self::$encodings) === 0) {
            self::loadDefaultEncodings();
        }

        self::registerEncoding($name, $encoding);
    }

    public static function getEncoding(string $name): Encoder
    {
        if(array_key_exists($name, self::$resolvedEncodings)) {
            return self::$resolvedEncodings[$name];
        }

        if(count(self::$encodings) === 0) {
            self::loadDefaultEncodings();
        }

        if(! array_key_exists($name, self::$encodings)) {
            throw new Exception("Unknown encoding {$name}.");
        }

        $callable = self::$encodings[$name];
        $encoding = $callable();

        if(! $encoding instanceof Encoder) {
            throw new Exception("EncodingContract {$name} must return " . Encoder::class);
        }

        self::$resolvedEncodings[$name] = $encoding;

        return $encoding;
    }

    protected static function loadDefaultEncodings(): void
    {
        foreach(self::$defaultEncodings as $name => $encoding) {
            [$class, $params] = $encoding;

            $encoder = new $class(...$params);

            self::registerEncoding($name, $encoder);
        }
    }
}