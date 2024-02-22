<?php

namespace Rahul900day\Tiktoken\Enums;

enum SpecialToken: string
{
    case ENDOFTEXT = '<|endoftext|>';
    case FIM_PREFIX = '<|fim_prefix|>';
    case FIM_MIDDLE = '<|fim_middle|>';
    case FIM_SUFFIX = '<|fim_suffix|>';
    case ENDOFPROMPT = '<|endofprompt|>';
}
