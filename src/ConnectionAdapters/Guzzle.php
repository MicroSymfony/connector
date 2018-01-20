<?php

namespace MicroSymfony\Connection\ConnectionAdapters;

use GuzzleHttp\Client;

class Guzzle extends AbstractAdapter implements ConnectionAdapterInterface
{
    /** @var Client */
    private $connection;

    public function requestRaw(string $method, string $uri, array $params = []): string
    {
        $result = $this->getConnection()->request($method, $uri, $params);

        return $result->getBody()->getContents();
    }

    private function getConnection(): Client
    {
        if (empty($this->connection)) {
            $this->connection = new Client();
        }

        return $this->connection;
    }

    /**
     * @param Client $connection
     */
    public function setConnection(Client $connection): void
    {
        $this->connection = $connection;
    }
}
