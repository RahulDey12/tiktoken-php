<?php

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Rahul900day\Tiktoken\Readers\HttpReader;

it('can read http response', function () {
    $client = mock(ClientInterface::class);
    $client->shouldReceive('request')->once()->andReturnUsing(function () {
        $response = mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->once()->andReturnUsing(function () {
            $stream = mock(StreamInterface::class);
            $stream->shouldReceive('getContents')->once()->andReturn('{"foo": "bar"}');

            return $stream;
        });

        return $response;
    });

    $reader = HttpReader::create($client);
    expect($reader->read('http://example.com/'))->toBe('{"foo": "bar"}');
});
