<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken;

use Closure;
use Rahul900day\Tiktoken\Contracts\EncodingContract;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\Cl100KBaseEncoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\Gpt2Encoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\P50KBaseEncoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\P50KEditEncoding;
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\R50KBaseEncoding;
use Rahul900day\Tiktoken\Exceptions\InvalidEncodingException;
use Rahul900day\Tiktoken\Exceptions\TiktokenException;

class Registry
{
    /** @var array<string, Encoder> */
    protected static array $resolvedEncodings = [];

    /** @var array<string, EncodingContract|Closure> */
    protected static array $encodings = [];

    /** @var non-empty-array<string, array{0: class-string, 1: array}> */
    public static array $defaultEncodings = [
        'gpt2' => [Gpt2Encoding::class, []],
        'r50k_base' => [R50KBaseEncoding::class, []],
        'p50k_base' => [P50KBaseEncoding::class, []],
        'p50k_edit' => [P50KEditEncoding::class, []],
        'cl100k_base' => [Cl100KBaseEncoding::class, []],
    ];

    protected static function registerEncoding(string $name, EncodingContract|Closure $encoding): void
    {
        if (isset(self::$resolvedEncodings[$name])) {
            unset(self::$resolvedEncodings[$name]);
        }

        self::$encodings[$name] = $encoding;
    }

    public static function registerCustomEncoding(string $name, EncodingContract|Closure $encoding): void
    {
        // Register default encodings before register any custom
        // encoding. This is how we can add replacement capabilities.
        if (self::$encodings === []) {
            self::loadDefaultEncodings();
        }

        self::registerEncoding($name, $encoding);
    }

    public static function getEncoding(string $name): Encoder
    {
        if (array_key_exists($name, self::$resolvedEncodings)) {
            return self::$resolvedEncodings[$name];
        }

        if (self::$encodings === []) {
            self::loadDefaultEncodings();
        }

        if (! array_key_exists($name, self::$encodings)) {
            throw new InvalidEncodingException($name);
        }

        $callable = self::$encodings[$name];
        $encoding = $callable();

        if (! $encoding instanceof Encoder) {
            throw new TiktokenException("Encoding {$name} must return a ".Encoder::class.' instance.');
        }

        self::$resolvedEncodings[$name] = $encoding;

        return $encoding;
    }

    protected static function loadDefaultEncodings(): void
    {
        foreach (self::$defaultEncodings as $name => $encoding) {
            [$class, $params] = $encoding;

            $encoder = new $class(...$params);

            self::registerEncoding($name, $encoder); // @phpstan-ignore-line
        }
    }
}
