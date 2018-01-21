<?php

namespace MicroSymfony\Test\Unit\DiscoveryAdapters;

use MicroSymfony\Connection\ConnectionAdapters\ConnectionAdapterInterface;
use MicroSymfony\Connection\DiscoveryAdapters\ConsulHttp;
use PHPUnit\Framework\TestCase;

class ConsulHttpTest extends TestCase
{
    public function testDiscover()
    {
        $discovery = new ConsulHttp();
        $discovery->setDiscoveryIp('1.1.1.1:8500');
        $connection = $this->getMockBuilder(ConnectionAdapterInterface::class)
            ->setMethods(['requestRaw'])
            ->getMockForAbstractClass();
        $connection->expects($this->any())
            ->method('requestRaw')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('1.1.1.1:8500/v1/catalog/service/testService')
            )
            ->willReturn('[{"Address":"2.2.2.2","ServicePort":"3333"}]');
        $discovery->setConnection($connection);

        $result = $discovery->discover('testService');

        $this->assertEquals('2.2.2.2:3333', $result);
    }
}
