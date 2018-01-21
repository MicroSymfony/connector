<?php

namespace MicroSymfony\Test\Unit\ConnectionAdapter;

use MicroSymfony\Connection\ConnectionAdapters\AbstractAdapter;
use MicroSymfony\Connection\DiscoveryAdapters\DiscoveryAdapterInterface;
use PHPUnit\Framework\TestCase;

final class AbstractAdapterTest extends TestCase
{
    public function testRequest()
    {
        $mock = $this->getMockForAbstractClass(AbstractAdapter::class);
        $mock->expects($this->any())
            ->method('requestRaw')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('123.11.1.0:122/test'),
                $this->equalTo(['headers' => ['Service' => 'service-from-test', 'Test' => 'OK']])
            )
            ->willReturn('["some":"data"]');
        $discover = $this->getMockForAbstractClass(DiscoveryAdapterInterface::class);
        $discover->expects($this->any())->method('discover')->willReturn('123.11.1.0:122');
        $mock->setDiscovery($discover);
        $mock->setServiceHeader('Service');
        $mock->setCallerService('service-from-test');
        $mock->addAdditionalHeader('Test', 'OK');

        $response = $mock->request('GET', 'myService/test');

        $this->assertEquals('["some":"data"]', $response);
    }

    public function testGet()
    {
        $mock = $this->getMockBuilder(AbstractAdapter::class)
            ->setMethods(['request'])
        ->getMockForAbstractClass();
        $mock->expects($this->any())
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('myService2/')
            )
            ->willReturn('["some":"data"]');

        $response = $mock->get('myService2/');
        $this->assertEquals('["some":"data"]', $response);
    }


    public function testPost()
    {
        $mock = $this->getMockBuilder(AbstractAdapter::class)
            ->setMethods(['request'])
            ->getMockForAbstractClass();
        $mock->expects($this->any())
            ->method('request')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo('myService3/posting')
            )
            ->willReturn('["result":"posted"]');

        $response = $mock->post('myService3/posting');
        $this->assertEquals('["result":"posted"]', $response);
    }
}
