<?php

namespace MicroSymfony\Connection\ConnectionAdapters;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use MicroSymfony\Connection\Exceptions\UnauthorizedException;

class Guzzle extends AbstractAdapter implements ConnectionAdapterInterface
{
    /** @var Client */
    private $connection;

    public function requestRaw(string $method, string $uri, array $params = []): string
    {
        try {
            $result = $this->getConnection()->request($method, $uri, $params);
        } catch (RequestException $exception) {
            if (403 === $exception->getCode()) {
                throw new UnauthorizedException('You are not authorized to use this service', $exception->getCode());
            } else {
                throw $exception;
            }
        }

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
