<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Readers;

use Http\Discovery\Psr17Factory;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Rahul900day\Tiktoken\Contracts\ReaderContract;

class HttpReader implements ReaderContract
{
    public function __construct(protected ClientInterface $client)
    {
        //
    }

    public static function create(?ClientInterface $client = null): HttpReader
    {
        $client ??= Psr18ClientDiscovery::find();

        return new self($client);
    }

    public function read(string|RequestInterface $location): string
    {
        $request = is_string($location) ? (new Psr17Factory())->createRequest('GET', $location) : $location;

        $response = $this->client->sendRequest($request);

        return $response->getBody()->getContents();
    }
}
