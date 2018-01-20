<?php

namespace MicroSymfony\Connection\DiscoveryAdapters;

interface DiscoveryAdapterInterface
{
    /**
     * @param string $serviceName given service name to lookup
     * @return string resolved service in format IP:PORT
     */
    public function discover(string $serviceName): string;
}
