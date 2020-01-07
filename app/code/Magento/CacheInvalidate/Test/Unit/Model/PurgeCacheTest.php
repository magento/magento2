<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Model;

use Zend\Uri\UriFactory;

class PurgeCacheTest extends \PHPUnit\Framework\TestCase
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
        $socketFactoryMock = $this->createMock(\Magento\CacheInvalidate\Model\SocketFactory::class);
        $this->socketAdapterMock = $this->createMock(\Zend\Http\Client\Adapter\Socket::class);
        $this->socketAdapterMock->expects($this->once())
            ->method('setOptions')
            ->with(['timeout' => 10]);
        $socketFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->socketAdapterMock);

        $this->loggerMock = $this->createMock(\Magento\Framework\Cache\InvalidateLogger::class);
        $this->cacheServer = $this->createMock(\Magento\PageCache\Model\Cache\Server::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\CacheInvalidate\Model\PurgeCache::class,
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
     * @dataProvider sendPurgeRequestDataProvider
     */
    public function testSendPurgeRequest($hosts)
    {
        $uris = [];
        /** @var array $host */
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
            $this->socketAdapterMock->expects($this->at($i++))
                ->method('read');
            $i++;
        }
        $this->socketAdapterMock->expects($this->exactly(count($uris)))
            ->method('close');

        $this->loggerMock->expects($this->once())
            ->method('execute');

        $this->assertTrue($this->model->sendPurgeRequest(['tags']));
    }

    public function testSendMultiPurgeRequest()
    {
        $tags = [
            '(^|,)cat_p_95(,|$)', '(^|,)cat_p_96(,|$)', '(^|,)cat_p_97(,|$)', '(^|,)cat_p_98(,|$)',
            '(^|,)cat_p_99(,|$)', '(^|,)cat_p_100(,|$)', '(^|,)cat_p_10038(,|$)', '(^|,)cat_p_142985(,|$)',
            '(^|,)cat_p_199(,|$)', '(^|,)cat_p_300(,|$)', '(^|,)cat_p_12038(,|$)', '(^|,)cat_p_152985(,|$)',
            '(^|,)cat_p_299(,|$)', '(^|,)cat_p_400(,|$)', '(^|,)cat_p_13038(,|$)', '(^|,)cat_p_162985(,|$)',
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
            ->willThrowException(new \Zend\Http\Client\Adapter\Exception\RuntimeException());
        $this->loggerMock->expects($this->never())
            ->method('execute');
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->assertFalse($this->model->sendPurgeRequest(['tags']));
    }
}
