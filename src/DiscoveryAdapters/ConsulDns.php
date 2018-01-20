<?php

namespace MicroSymfony\Connection\DiscoveryAdapters;

use MicroSymfony\Connection\Exceptions\LookupFailureException;
use MicroSymfony\Connection\Exceptions\ServiceNotFoundException;

class ConsulDns implements DiscoveryAdapterInterface
{
    /** @var string[] */
    private $nameServers = [];
    /** @var \Net_DNS2_Resolver */
    private $resolver;

    /**
     * @param string $serviceName given service name to lookup
     * @return string resolved service in format IP:PORT
     */
    public function discover(string $serviceName): string
    {
        $serviceDomain = sprintf('%s.service.consul', $serviceName);

        $table = $this->lookupAll($serviceDomain);

        if (empty($table)) {
            throw new ServiceNotFoundException();
        }

        $entry = $table[mt_rand(0, count($table)-1)];

        return $entry;
    }

    /**
     * @param string[] $nameServers
     */
    public function setNameServers(array $nameServers): void
    {
        $this->nameServers = $nameServers;
    }

    private function getResolver()
    {
        if (null === $this->resolver) {
            $this->resolver = new \Net_DNS2_Resolver();
        }

        return $this->resolver;
    }

    private function lookupAll($serviceDomain)
    {
        $success = false;
        $entries = [];
        foreach ($this->getNameServers() as $nameServer) {
            try {
                $table = $this->dnsLookup($serviceDomain, $nameServer);
                $success = true;
                $entries = array_merge($entries, $table);
            } catch (LookupFailureException $exception) {
                // do nothing, will throw another exception later
            }
        }

        if (!$success) {
            throw new LookupFailureException();
        }

        $entries = array_unique($entries);

        return $entries;
    }

    private function dnsLookup($serviceDomain, $nameServer)
    {
        [$ip, $port] = explode(':', $nameServer);
        $resolver = $this->getResolver();
        $resolver->nameservers = [$ip];
        $resolver->dns_port = $port;

        try {
            $records = $resolver->query($serviceDomain, 'SRV');
        } catch (\Net_DNS2_Exception $exception) {
            throw new LookupFailureException();
        }

        $table = $this->parseResult($records);

        return $table;
    }

    private function parseResult(\Net_DNS2_RR $records)
    {
        $table = [];
        foreach ($records->additional as $record) {
            if ($record instanceof \Net_DNS2_RR_A) {
                $table[$record->name] = $record->address;
            }
        }
        foreach ($records->answer as $record) {
            if ($record instanceof \Net_DNS2_RR_SRV) {
                $table[$record->target] .= ':'.$record->port;
            }
        }

        $table = array_values($table);

        return $table;
    }

    private function getNameServers()
    {
        return $this->nameServers;
    }
}
