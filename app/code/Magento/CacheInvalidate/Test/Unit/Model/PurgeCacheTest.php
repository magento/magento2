<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

use \Zend\Uri\UriFactory;

class PurgeCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CacheInvalidate\Model\PurgeCache */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Zend\Http\Client\Adapter\Socket */
    protected $socketAdapterMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Cache\InvalidateLogger */
    protected $loggerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\PageCache\Model\Cache\Server */
    protected $cacheServer;

    protected function setUp()
    {
        $socketFactoryMock = $this->getMock(\Magento\CacheInvalidate\Model\SocketFactory::class, [], [], '', false);
        $this->socketAdapterMock = $this->getMock(\Zend\Http\Client\Adapter\Socket::class, [], [], '', false);
        $this->socketAdapterMock->expects($this->once())
            ->method('setOptions')
            ->with(['timeout' => 10]);
        $socketFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->socketAdapterMock);

        $this->loggerMock = $this->getMock(\Magento\Framework\Cache\InvalidateLogger::class, [], [], '', false);
        $this->cacheServer = $this->getMock(\Magento\PageCache\Model\Cache\Server::class, [], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\CacheInvalidate\Model\PurgeCache::class,
            [
                'cacheServer' => $this->cacheServer,
                'socketAdapterFactory' => $socketFactoryMock,
                'logger' => $this->loggerMock,
            ]
        );
    }

    /**
     * @param string[] $hosts
     * @dataProvider sendPurgeRequestDataProvider
     */
    public function testSendPurgeRequest($hosts)
    {
        $uris = [];
        foreach ($hosts as $host) {
            $port = isset($host['port']) ? $host['port'] : \Magento\PageCache\Model\Cache\Server::DEFAULT_PORT;
            $uris[] = UriFactory::factory('')->setHost($host['host'])
                ->setPort($port)
                ->setScheme('http');
        }
        $this->cacheServer->expects($this->once())
            ->method('getUris')
            ->willReturn($uris);

        $i = 1;
        foreach ($uris as $uri) {
            $this->socketAdapterMock->expects($this->at($i++))
                ->method('connect')
                ->with($uri->getHost(), $uri->getPort());
            $this->socketAdapterMock->expects($this->at($i++))
                ->method('write')
                ->with('PURGE', $uri, '1.1', ['X-Magento-Tags-Pattern' => 'tags', 'Host' => $uri->getHost()]);
            $i++;
        }
        $this->socketAdapterMock->expects($this->exactly(count($uris)))
            ->method('close');

        $this->loggerMock->expects($this->once())
            ->method('execute');

        $this->assertTrue($this->model->sendPurgeRequest('tags'));
    }

    public function sendPurgeRequestDataProvider()
    {
        return [
            [
                [['host' => '127.0.0.1', 'port' => 8080],]
            ],
            [
                [
                    ['host' => '127.0.0.1', 'port' => 8080],
                    ['host' => '127.0.0.2', 'port' => 1234],
                    ['host' => 'host']
                ]
            ]
        ];
    }

    public function testSendPurgeRequestWithException()
    {
        $uris[] = UriFactory::factory('')->setHost('127.0.0.1')
            ->setPort(8080)
            ->setScheme('http');

        $this->cacheServer->expects($this->once())
            ->method('getUris')
            ->willReturn($uris);
        $this->socketAdapterMock->method('connect')
            ->willThrowException(new \Zend\Http\Client\Adapter\Exception\RuntimeException());
        $this->loggerMock->expects($this->never())
            ->method('execute');
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->assertFalse($this->model->sendPurgeRequest('tags'));
    }
}
