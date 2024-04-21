<?php

declare(strict_types=1);

namespace Rahul900day\Tiktoken\Readers;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use Rahul900day\Tiktoken\Contracts\ReaderContract;

class HttpReader implements ReaderContract
{
    public function __construct(protected ClientInterface $client)
    {
        //
    }

    public static function create(?ClientInterface $client = null): static
    {
        $client ??= new Client();

        return new static($client);
    }

    public function read(string $location): string
    {
        $response = $this->client->request('GET', $location);

        return $response->getBody()->getContents();
    }
}
