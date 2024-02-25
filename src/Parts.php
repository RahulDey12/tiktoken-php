<?php

namespace Rahul900day\Tiktoken;

class Parts
{
    public function __construct(public array $parts = [])
    {
    }

    public function push(array $part)
    {
        $this->parts[] = $part;
        
        return $this;
    }

    public function ()
    {
        
    }
}