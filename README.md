# ⏳ Tiktoken for PHP

Most compatible PHP implementation of OpenAI's original [Tiktoken](https://github.com/openai/tiktoken).

## Get Started

> Requires [PHP 8.1+](https://php.net/releases/)

Install Tiktoken via [Composer](https://getcomposer.org/) package manager.

```shell
composer require rahul900day/tiktoken-php
```

## Supported Models

| Model       |Supported|
|-------------|---------|
| GPT-3       |:white_check_mark:|
| GPT-3.5 & 4 | :white_check_mark: |
| GPT-4o | :x: |

## Usage

### Basic Usage

```php
use Rahul900day\Tiktoken\Tiktoken;

$encoder = Tiktoken::getEncodingForModel('gpt-4');
$encoder->encode("hello world aaaaaaaaaaaa");
$encoder->decode([9906, 4435]);
```

### Special Tokens

```php
use Rahul900day\Tiktoken\Tiktoken;

$encoder = Tiktoken::getEncodingForModel('gpt-4');
$encoder->encode('<|endoftext|>', allowedSpecial: 'all');
```

### Caching

Tiktoken always cache the server's responses when downloading them.

By default it uses the system's default temporary directory to cache a response but you
can still overwrite the cache location by setting `TIKTOKEN_CACHE_DIR` environment variable.

### Registering Custom Encoding

```php
use Rahul900day\Tiktoken\Encodings\OpenAiPublic\Cl100KBaseEncoding;

class Cl100KIm extends Cl100KBaseEncoding 
{
    protected function getName(): string
    {
        return 'cl100k_im';
    }
    
    protected function getSpecialTokens(): array
    {
        return [
            ...parent::getSpecialTokens(),
            "<|im_start|>" => 100264,
            "<|im_end|>" => 100265,
        ];
    }
}

use Rahul900day\Tiktoken\Registry;
use Rahul900day\Tiktoken\Tiktoken;

Registry::registerCustomEncoding('cl100k_im', new Cl100KIm);
$encoding = Tiktoken::getEncoding('cl100k_im');

// Expect: 100264
$encoding->encode("<|im_start|>", allowedSpecial: 'all');

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Rahul Dey](https://github.com/RahulDey12)
- [All Contributors](https://github.com/RahulDey12/tiktoken-php/graphs/contributors)

## License

This package is released under the [MIT License](https://github.com/RahulDey12/tiktoken-php/blob/main/LICENSE.md).
