<?php

namespace MicroSymfony\Test\Unit\DiscoveryAdapters;

use MicroSymfony\Connection\ConnectionAdapters\ConnectionAdapterInterface;
use MicroSymfony\Connection\DiscoveryAdapters\ConsulHttp;
use MicroSymfony\Connection\Exceptions\ServiceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConsulHttpTest extends TestCase
{
    public function testDiscover()
    {
        $discovery = new ConsulHttp();
        $discovery->setDiscoveryIp('1.1.1.1:8500');
        /** @var ConnectionAdapterInterface|MockObject $connection */
        $connection = $this->getMockBuilder(ConnectionAdapterInterface::class)
            ->setMethods(['requestRaw'])
            ->getMockForAbstractClass();
        $connection->expects($this->any())
            ->method('requestRaw')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('1.1.1.1:8500/v1/catalog/service/testService')
            )
            ->willReturn('[{"ServiceAddress":"2.2.2.2","ServicePort":"3333"}]');
        $discovery->setConnection($connection);

        $result = $discovery->discover('testService');

        $this->assertEquals('2.2.2.2:3333', $result);
    }

    public function testDiscoverFromMultiple()
    {
        $discovery = new ConsulHttp();
        $discovery->setDiscoveryIp('1.1.1.1:8500');
        /** @var ConnectionAdapterInterface|MockObject $connection */
        $connection = $this->getMockBuilder(ConnectionAdapterInterface::class)
            ->setMethods(['requestRaw'])
            ->getMockForAbstractClass();
        $connection->expects($this->any())
            ->method('requestRaw')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('1.1.1.1:8500/v1/catalog/service/testService')
            )
            ->willReturn(
                '[{"ServiceAddress":"2.2.2.2","ServicePort":"3333"},{"ServiceAddress":"2.2.2.1","ServicePort":"4444"}]'
            );
        $discovery->setConnection($connection);

        $result = $discovery->discover('testService');

        $this->assertContains($result, ['2.2.2.2:3333', '2.2.2.1:4444']);
    }

    public function testDiscoverNonExistingService()
    {
        $discovery = new ConsulHttp();
        $discovery->setDiscoveryIp('1.1.1.1:8500');
        /** @var ConnectionAdapterInterface|MockObject $connection */
        $connection = $this->getMockBuilder(ConnectionAdapterInterface::class)
            ->setMethods(['requestRaw'])
            ->getMockForAbstractClass();
        $connection->expects($this->any())
            ->method('requestRaw')
            ->withConsecutive(
                [$this->equalTo('GET'), $this->equalTo('1.1.1.1:8500/v1/catalog/service/testService')],
                [$this->equalTo('GET'), $this->equalTo('1.1.1.1:8500/v1/catalog/services')]
            )
            ->willReturnOnConsecutiveCalls('[]', '[]');
        $discovery->setConnection($connection);

        $this->expectException(ServiceNotFoundException::class);
        $discovery->discover('testService');
    }

    public function testDiscoverPrefixedService()
    {
        $discovery = new ConsulHttp();
        $discovery->setDiscoveryIp('1.1.1.1:8500');
        /** @var ConnectionAdapterInterface|MockObject $connection */
        $connection = $this->getMockBuilder(ConnectionAdapterInterface::class)
            ->setMethods(['requestRaw'])
            ->getMockForAbstractClass();
        $connection->expects($this->any())
            ->method('requestRaw')
            ->withConsecutive(
                [$this->equalTo('GET'), $this->equalTo('1.1.1.1:8500/v1/catalog/service/testService')],
                [$this->equalTo('GET'), $this->equalTo('1.1.1.1:8500/v1/catalog/services')],
                [$this->equalTo('GET'), $this->equalTo('1.1.1.1:8500/v1/catalog/service/prefix_testService-80')]
            )
            ->willReturnOnConsecutiveCalls(
                '[]',
                '{"testService122":[],"prefix_testService-80":[]}',
                '[{"ServiceAddress":"2.2.2.2","ServicePort":"3333"},{"ServiceAddress":"2.2.2.1","ServicePort":"4444"}]'
            );

        $discovery->setConnection($connection);

        $result = $discovery->discover('testService');

        $this->assertContains($result, ['2.2.2.2:3333', '2.2.2.1:4444']);
    }
}
