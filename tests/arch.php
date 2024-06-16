<?php

use Rahul900day\Tiktoken\Contracts\CacheContract;
use Rahul900day\Tiktoken\Contracts\EncodingContract;
use Rahul900day\Tiktoken\Contracts\ReaderContract;
use Rahul900day\Tiktoken\Exceptions\TiktokenException;
use Rahul900day\Tiktoken\Loaders\Loader;

arch('globals')->expect(['dd', 'dump', 'echo'])->not->toBeUsed();

arch('enums')
    ->expect('Rahul900day\Tiktoken\Enums')
    ->toBeEnum();

arch('interface')
    ->expect('Rahul900day\Tiktoken\Contracts')
    ->toBeInterface();

arch('cache')
    ->expect('Rahul900day\Tiktoken\Cache')
    ->toImplement(CacheContract::class);

arch('encoding')
    ->expect('Rahul900day\Tiktoken\Encodings')
    ->toImplement(EncodingContract::class);

arch('loader')
    ->expect('Rahul900day\Tiktoken\Loaders')
    ->toExtend(Loader::class);

arch('reader')
    ->expect('Rahul900day\Tiktoken\Readers')
    ->toImplement(ReaderContract::class);

arch('exception')
    ->expect('Rahul900day\Tiktoken\Exceptions')
    ->toExtend(TiktokenException::class)
    ->toHaveSuffix('Exception');
