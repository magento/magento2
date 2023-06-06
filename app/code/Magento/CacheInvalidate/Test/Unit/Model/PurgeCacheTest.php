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
    /**
     * @var PurgeCache
     */
    protected $model;

    /**
     * @var MockObject|Socket
     */
    protected $socketAdapterMock;

    /**
     * @var MockObject|InvalidateLogger
     */
    protected $loggerMock;

    /**
     * @var MockObject|Server
     */
    protected $cacheServer;

    /**
     * @inheritDoc
     */
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
                'maxHeaderSize' => 256
            ]
        );
    }

    /**
     * @param string[] $hosts
     *
     * @return void
     * @dataProvider sendPurgeRequestDataProvider
     */
    public function testSendPurgeRequest(array $hosts): void
    {
        $uris = [];
        /** @var array $host */
        foreach ($hosts as $host) {
            $port = $host['port'] ?? Server::DEFAULT_PORT;
            $uris[] = UriFactory::factory('')->setHost($host['host'])
                ->setPort($port)
                ->setScheme('http');
        }
        $this->cacheServer->expects($this->once())
            ->method('getUris')
            ->willReturn($uris);
        $connectWithArgs = $writeWithArgs = [];

        foreach ($uris as $uri) {
            $writeWithArgs[] = ['PURGE', $uri, '1.1', ['X-Magento-Tags-Pattern' => 'tags', 'Host' => $uri->getHost()]];
        }
        $this->socketAdapterMock
            ->method('connect')
            ->withConsecutive(...$connectWithArgs);
        $this->socketAdapterMock
            ->method('write')
            ->withConsecutive(...$writeWithArgs);
        $this->socketAdapterMock
            ->method('read');

        $this->socketAdapterMock->expects($this->exactly(count($uris)))
            ->method('close');

        $this->loggerMock->expects($this->once())
            ->method('execute');

        $this->assertTrue($this->model->sendPurgeRequest(['tags']));
    }

    /**
     * @return void
     */
    public function testSendMultiPurgeRequest(): void
    {
        $tags = [
            '(^|,)cat_p_95(,|$)', '(^|,)cat_p_96(,|$)', '(^|,)cat_p_97(,|$)', '(^|,)cat_p_98(,|$)',
            '(^|,)cat_p_99(,|$)', '(^|,)cat_p_100(,|$)', '(^|,)cat_p_10038(,|$)', '(^|,)cat_p_142985(,|$)',
            '(^|,)cat_p_199(,|$)', '(^|,)cat_p_300(,|$)', '(^|,)cat_p_12038(,|$)', '(^|,)cat_p_152985(,|$)',
            '(^|,)cat_p_299(,|$)', '(^|,)cat_p_400(,|$)', '(^|,)cat_p_13038(,|$)', '(^|,)cat_p_162985(,|$)'
        ];

        $tagsSplitA = array_slice($tags, 0, 12);
        $tagsSplitB = array_slice($tags, 12, 4);

        $uri =  UriFactory::factory('')->setHost('localhost')
            ->setPort(80)
            ->setScheme('http');

        $this->cacheServer->expects($this->once())
            ->method('getUris')
            ->willReturn([$uri]);

        $this->socketAdapterMock->expects($this->exactly(2))
            ->method('connect')
            ->with($uri->getHost(), $uri->getPort());

        $this->socketAdapterMock->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                [
                    'PURGE', $uri, '1.1',
                    ['X-Magento-Tags-Pattern' => implode('|', $tagsSplitA), 'Host' => $uri->getHost()]
                ],
                [
                    'PURGE', $uri, '1.1',
                    ['X-Magento-Tags-Pattern' => implode('|', $tagsSplitB), 'Host' => $uri->getHost()]
                ]
            );

        $this->socketAdapterMock->expects($this->exactly(2))
            ->method('close');

        $this->assertTrue($this->model->sendPurgeRequest($tags));
    }

    /**
     * @return array
     */
    public function sendPurgeRequestDataProvider(): array
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

    /**
     * @return void
     */
    public function testSendPurgeRequestWithException(): void
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

        $this->assertFalse($this->model->sendPurgeRequest(['tags']));
    }
}
