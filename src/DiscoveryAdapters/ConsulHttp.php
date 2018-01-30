<?php

namespace MicroSymfony\Connection\DiscoveryAdapters;

use MicroSymfony\Connection\ConnectionAdapters\ConnectionAdapterInterface;
use MicroSymfony\Connection\Exceptions\ServiceNotFoundException;

class ConsulHttp implements DiscoveryAdapterInterface
{
    /** @var string */
    private $discoveryIp = '';
    /** @var ConnectionAdapterInterface */
    private $connection;

    /**
     * @param string $serviceName given service name to lookup
     * @return string resolved service in format IP:PORT
     */
    public function discover(string $serviceName): string
    {
        $result = $this->connection->requestRaw('GET', $this->discoveryIp.'/v1/catalog/service/'.$serviceName);
        $data = json_decode($result, true);

        if (empty($data)) {
            // service name might be pre-fixed due to docker compose
            $result = $this->connection->requestRaw('GET', $this->discoveryIp.'/v1/catalog/services');
            $list = json_decode($result, true);
            foreach ((array)$list as $service => $tags) {
                if (preg_match('/.*?_'.$serviceName.'-80/', $service)) {
                    $result = $this->connection->requestRaw('GET', $this->discoveryIp.'/v1/catalog/service/'.$service);
                    $data = json_decode($result, true);
                    break;
                }
            }
        }

        if (empty($data)) {
            throw new ServiceNotFoundException();
        }

        $item = $data[mt_rand(0, count($data) - 1)];

        $service = $item['ServiceAddress'].':'.$item['ServicePort'];

        return $service;
    }

    /**
     * @param ConnectionAdapterInterface $connection
     */
    public function setConnection(ConnectionAdapterInterface $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @param string $discoveryIp
     */
    public function setDiscoveryIp(string $discoveryIp): void
    {
        $this->discoveryIp = $discoveryIp;
    }
}
