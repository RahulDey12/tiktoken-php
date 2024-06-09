<?php

use Rahul900day\Tiktoken\Contracts\CacheContract;
use Rahul900day\Tiktoken\Contracts\ReaderContract;
use Rahul900day\Tiktoken\Exceptions\InvalidChecksumException;
use Rahul900day\Tiktoken\Loaders\Loader;

beforeEach(function () {
    $this->reader = mock(ReaderContract::class);
    $this->cache = mock(CacheContract::class);

    $this->loader = new class($this->reader, $this->cache) extends Loader
    {
        public function load(string $path, ?string $hash = null)
        {
            return $this->readFileCached($path, $hash);
        }
    };
});

it('can load without cache', function () {
    $this->reader->shouldReceive('read')->once()->andReturn('fake_data');

    $this->cache->shouldReceive('has')->once()->andReturn(false);
    $this->cache->shouldReceive('set')->once();

    expect($this->loader->load('fake.tiktoken'))->toBe('fake_data');
});

it('can load with cache', function () {
    $this->reader->shouldNotReceive('read');

    $this->cache->shouldReceive('has')->once()->andReturn(true);
    $this->cache->shouldReceive('get')->once()->andReturn('fake_data');

    expect($this->loader->load('fake.tiktoken'))->toBe('fake_data');
});

it('can throw error when file hash doesn\'t match', function () {
    $this->reader->shouldReceive('read')->once()->andReturn('fake_data');

    $this->cache->shouldReceive('has')->once()->andReturn(false);

    $this->loader->load('fake.tiktoken', 'xxxfkhash');
})->throws(InvalidChecksumException::class);

it('can re-fetch data when cache hash doesn\'t match', function () {
    $this->reader->shouldReceive('read')->andReturn('fake_data');

    $this->cache->shouldReceive('has')->once()->andReturn(true);
    $this->cache->shouldReceive('get')->once()->andReturn('fake_fake_data');
    $this->cache->shouldReceive('set')->once();
    $this->cache->shouldReceive('delete')->once();

    expect($this->loader->load('fake.tiktoken', hash('sha256', 'fake_data')))
        ->toBe('fake_data');
});
