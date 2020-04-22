<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CacheInvalidate\Test\Unit\Model;

use Laminas\Http\Client\Adapter\Exception\RuntimeException;
use Laminas\Http\Client\Adapter\Socket;
use Laminas\Uri\UriFactory;
use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\CacheInvalidate\Model\SocketFactory;
use Magento\Framework\Cache\InvalidateLogger;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Cache\Server;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PurgeCacheTest extends TestCase
{
    /** @var PurgeCache */
    protected $model;

    /** @var MockObject|Socket */
    protected $socketAdapterMock;

    /** @var MockObject|InvalidateLogger */
    protected $loggerMock;

    /** @var MockObject|Server */
    protected $cacheServer;

    protected function setUp(): void
    {
        $socketFactoryMock = $this->createMock(SocketFactory::class);
        $this->socketAdapterMock = $this->createMock(Socket::class);
        $this->socketAdapterMock->expects($this->once())
            ->method('setOptions')
            ->with(['timeout' => 10]);
        $socketFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->socketAdapterMock);

        $this->loggerMock = $this->createMock(InvalidateLogger::class);
        $this->cacheServer = $this->createMock(Server::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            PurgeCache::class,
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
            $port = isset($host['port']) ? $host['port'] : Server::DEFAULT_PORT;
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
            $this->socketAdapterMock->expects($this->at($i++))
                ->method('read');
            $i++;
        }
        $this->socketAdapterMock->expects($this->exactly(count($uris)))
            ->method('close');

        $this->loggerMock->expects($this->once())
            ->method('execute');

        $this->assertTrue($this->model->sendPurgeRequest('tags'));
    }

    /**
     * @return array
     */
    public function sendPurgeRequestDataProvider()
    {
        return [
            [
                [['host' => '127.0.0.1', 'port' => 8080]]
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
            ->willThrowException(new RuntimeException());
        $this->loggerMock->expects($this->never())
            ->method('execute');
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->assertFalse($this->model->sendPurgeRequest('tags'));
    }
}
