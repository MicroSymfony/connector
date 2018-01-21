<?php

namespace MicroSymfony\Connection\ConnectionAdapters;

use MicroSymfony\Connection\DiscoveryAdapters\DiscoveryAdapterInterface;

abstract class AbstractAdapter implements ConnectionAdapterInterface
{
    /** @var string[] */
    protected $serviceIps = [];
    /** @var DiscoveryAdapterInterface */
    protected $discovery;
    /** @var string */
    protected $serviceHeader;
    /** @var array */
    protected $additionalHeaders = [];
    /** @var string */
    protected $callerService;

    /**
     * @inheritdoc
     */
    abstract public function requestRaw(string $method, string $uri, array $params = []): string;

    /**
     * @inheritdoc
     */
    public function request(string $method, string $endpoint, array $params = []): string
    {
        $serviceInfo = explode('/', $endpoint, 2);
        $serviceName = $serviceInfo[0];
        $path = $serviceInfo[1] ?? '';

        $serviceIp = $this->getServiceIp($serviceName);

        $uri = $serviceIp.'/'.$path;

        $params['headers'] = $this->mergeHeaders($params['headers'] ?? []);

        return $this->requestRaw($method, $uri, $params);
    }

    /**
     * @inheritdoc
     */
    public function get(string $endpoint, array $params = []): string
    {
        return $this->request('GET', $endpoint, $params);
    }

    /**
     * @inheritdoc
     */
    public function post(string $endpoint, array $params = []): string
    {
        return $this->request('POST', $endpoint, $params);
    }

    /**
     * @param DiscoveryAdapterInterface $discovery
     */
    public function setDiscovery(DiscoveryAdapterInterface $discovery): void
    {
        $this->discovery = $discovery;
    }

    /**
     * Get service IP from service discovery
     *
     * @param string $serviceName
     * @param bool   $force
     * @return string
     */
    protected function getServiceIp(string $serviceName, bool $force = false): string
    {
        if (!isset($this->serviceIps[$serviceName]) || $force) {
            $serviceIp = $this->discovery->discover($serviceName);
            $this->serviceIps[$serviceName] = $serviceIp;
        }

        return $this->serviceIps[$serviceName];
    }

    protected function mergeHeaders(array $headers = []): array
    {
        if (!empty($this->serviceHeader)) {
            $headers = array_merge($headers, [$this->serviceHeader => $this->callerService]);
        }
        if (!empty($this->additionalHeaders)) {
            $headers = array_merge($headers, $this->additionalHeaders);
        }

        return $headers;
    }

    /**
     * @param array $additionalHeaders
     */
    public function setAdditionalHeaders(array $additionalHeaders): void
    {
        $this->additionalHeaders = $additionalHeaders;
    }

    /**
     * @param string $header
     * @param string|int $value
     */
    public function addAdditionalHeader(string $header, $value): void
    {
        $this->additionalHeaders[$header] = $value;
    }

    /**
     * @param string $serviceHeader
     */
    public function setServiceHeader(string $serviceHeader): void
    {
        $this->serviceHeader = $serviceHeader;
    }

    /**
     * @param string $callerService
     */
    public function setCallerService(string $callerService = null): void
    {
        $this->callerService = $callerService;
    }
}
