<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Enums;

use Rahul900day\Tiktoken\Exceptions\InvalidPatternException;

enum SpecialToken: string
{
    case STARTOFTEXT = '<|startoftext|>';
    case ENDOFTEXT = '<|endoftext|>';
    case FIM_PREFIX = '<|fim_prefix|>';
    case FIM_MIDDLE = '<|fim_middle|>';
    case FIM_SUFFIX = '<|fim_suffix|>';
    case ENDOFPROMPT = '<|endofprompt|>';

    /**
     * @param array<string> $tokens
     * @return string
     * @throws InvalidPatternException
     */
    public static function getRegex(array $tokens): string
    {
        $parts = array_map('preg_quote', $tokens);
        $regex = '/'.implode('|', $parts).'/u';

        if (@preg_match($regex, '') === false) {
            throw new InvalidPatternException($regex);
        }

        return $regex;
    }
}
