<?php

use Rahul900day\Tiktoken\Cache\FileSystemCache;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

beforeEach(function () {
    $cache = new PhpFilesAdapter(
        namespace: 'tiktoken-cache',
        directory: sys_get_temp_dir(),
    );

    $cache->delete('test');
});

it('can create/update cache value', function (string $value) {
    $cache = new FilesystemCache();

    $cache->set('test', $value);

    expect($cache->get('test'))->toBe($value);
})->with(['value', 'value1', 'random1']);

it('can delete cache', function () {
    $cache = new FilesystemCache();

    $cache->set('test', 'random value');
    expect($cache->get('test'))->toBe('random value');

    $cache->delete('test');
    expect($cache->get('test'))->toBeNull();
});

it('can check if cache exists', function () {
    $cache = new FilesystemCache();

    expect($cache->has('test'))->toBeFalse();

    $cache->set('test', 'random value');

    expect($cache->has('test'))->toBeTrue();
});
