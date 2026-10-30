<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CacheInvalidate\Test\Unit\Model;

use Laminas\Uri\Uri;
use Laminas\Uri\UriFactory;
use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\CacheInvalidate\Model\SocketFactory;
use Magento\Framework\Cache\InvalidateLogger;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Cache\Server;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PurgeCacheTest extends TestCase
{
    /**
     * @var PurgeCache
     */
    protected $model;

    /**
     * @var MockObject|CurlFactory
     */
    private $curlFactory;

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
        $socketFactoryMock->expects($this->never())->method('create');
        $this->curlFactory = $this->createMock(CurlFactory::class);
        $this->loggerMock = $this->createMock(InvalidateLogger::class);
        $this->cacheServer = $this->createMock(Server::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            PurgeCache::class,
            [
                'cacheServer' => $this->cacheServer,
                'socketAdapterFactory' => $socketFactoryMock,
                'logger' => $this->loggerMock,
                'maxHeaderSize' => 256,
                'curlFactory' => $this->curlFactory
            ]
        );
    }

    /**
     * @param array $hosts
     * @return void
     */
    #[DataProvider('sendPurgeRequestDataProvider')]
    public function testSendPurgeRequest(array $hosts): void
    {
        $uris = [];
        $clients = [];
        foreach ($hosts as $host) {
            $uri = $this->createUri($host['host'], $host['port'] ?? Server::DEFAULT_PORT);
            $uris[] = $uri;
            $clients[] = $this->createClient($uri, 'tags');
        }
        $this->cacheServer->expects($this->once())->method('getUris')->willReturn($uris);
        $this->curlFactory->expects($this->exactly(count($clients)))->method('create')->willReturn(...$clients);
        $this->loggerMock->expects($this->never())->method('critical');
        $this->loggerMock->expects($this->never())->method('warning');
        $this->loggerMock->expects($this->once())->method('execute')->with([
            'servers' => $uris,
            'formattedTagsChunk' => 'tags'
        ]);

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

        $uri = $this->createUri('localhost', 80);
        $firstChunk = implode('|', array_slice($tags, 0, 12));
        $secondChunk = implode('|', array_slice($tags, 12, 4));
        $this->cacheServer->expects($this->once())->method('getUris')->willReturn([$uri]);
        $this->curlFactory->expects($this->exactly(2))->method('create')->willReturn(
            $this->createClient($uri, $firstChunk),
            $this->createClient($uri, $secondChunk)
        );
        $this->loggerMock->expects($this->exactly(2))->method('execute')->willReturnCallback(
            function (array $context) use ($uri, $firstChunk, $secondChunk): void {
                $this->assertSame([$uri], $context['servers']);
                $this->assertContains($context['formattedTagsChunk'], [$firstChunk, $secondChunk]);
            }
        );

        $this->assertTrue($this->model->sendPurgeRequest($tags));
    }

    /**
     * @return void
     */
    public function testSendPurgeRequestContinuesAfterFailedChunk(): void
    {
        $uri = $this->createUri('127.0.0.1', 8080);
        $firstChunk = str_repeat('a', 256);
        $this->cacheServer->expects($this->once())->method('getUris')->willReturn([$uri]);
        $this->curlFactory->expects($this->exactly(2))->method('create')->willReturn(
            $this->createClient($uri, $firstChunk, 'Timed out'),
            $this->createClient($uri, 'remaining')
        );
        $this->loggerMock->expects($this->once())->method('critical')->with(
            'No cache server(s) could be purged '
            . 'Cache host: 127.0.0.1:8080resulted in error message: Timed out',
            ['servers' => [$uri], 'formattedTagsChunk' => $firstChunk]
        );
        $this->loggerMock->expects($this->never())->method('warning');
        $this->loggerMock->expects($this->once())->method('execute')->with([
            'servers' => [$uri],
            'formattedTagsChunk' => 'remaining'
        ]);

        $this->assertFalse($this->model->sendPurgeRequest([$firstChunk, 'remaining']));
    }

    /**
     * @return array
     */
    public static function sendPurgeRequestDataProvider(): array
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
        $uri = $this->createUri('127.0.0.1', 8080);
        $this->cacheServer->expects($this->once())->method('getUris')->willReturn([$uri]);
        $this->curlFactory->expects($this->once())->method('create')->willReturn(
            $this->createClient($uri, 'tags', 'Connection failed')
        );
        $this->loggerMock->expects($this->never())->method('execute');
        $this->loggerMock->expects($this->never())->method('warning');
        $this->loggerMock->expects($this->once())->method('critical')->with(
            'No cache server(s) could be purged '
            . 'Cache host: 127.0.0.1:8080resulted in error message: Connection failed',
            ['servers' => [$uri], 'formattedTagsChunk' => 'tags']
        );

        $this->assertFalse($this->model->sendPurgeRequest(['tags']));
    }

    /**
     * @return void
     */
    public function testSendPurgeRequestWithPartialFailure(): void
    {
        $uris = [$this->createUri('127.0.0.1', 8080), $this->createUri('127.0.0.2', 1234)];
        $this->cacheServer->expects($this->once())->method('getUris')->willReturn($uris);
        $this->curlFactory->expects($this->exactly(2))->method('create')->willReturn(
            $this->createClient($uris[0], 'tags', 'Connection failed'),
            $this->createClient($uris[1], 'tags')
        );
        $context = ['servers' => $uris, 'formattedTagsChunk' => 'tags'];
        $this->loggerMock->expects($this->never())->method('critical');
        $this->loggerMock->expects($this->once())->method('warning')->with(
            'Unresponsive cache server(s) hit'
            . 'Cache host: 127.0.0.1:8080resulted in error message: Connection failed',
            $context
        );
        $this->loggerMock->expects($this->once())->method('execute')->with($context);

        $this->assertTrue($this->model->sendPurgeRequest(['tags']));
    }

    /**
     * @return void
     */
    public function testSendPurgeRequestWithAllServersFailing(): void
    {
        $uris = [$this->createUri('127.0.0.1', 8080), $this->createUri('127.0.0.2', 1234)];
        $this->cacheServer->expects($this->once())->method('getUris')->willReturn($uris);
        $this->curlFactory->expects($this->exactly(2))->method('create')->willReturn(
            $this->createClient($uris[0], 'tags', 'Connection failed'),
            $this->createClient($uris[1], 'tags', 'Timed out')
        );
        $this->loggerMock->expects($this->never())->method('execute');
        $this->loggerMock->expects($this->never())->method('warning');
        $this->loggerMock->expects($this->once())->method('critical')->with(
            'No cache server(s) could be purged '
            . 'Cache host: 127.0.0.1:8080resulted in error message: Connection failed '
            . 'Cache host: 127.0.0.2:1234resulted in error message: Timed out',
            ['servers' => $uris, 'formattedTagsChunk' => 'tags']
        );

        $this->assertFalse($this->model->sendPurgeRequest(['tags']));
    }

    /**
     * @return void
     */
    public function testSendPurgeRequestIgnoresHttpErrorStatus(): void
    {
        $uri = $this->createUri('127.0.0.1', 8080);
        $client = $this->createClient($uri, 'tags');
        $client->expects($this->never())->method('getStatus');
        $this->cacheServer->expects($this->once())->method('getUris')->willReturn([$uri]);
        $this->curlFactory->expects($this->once())->method('create')->willReturn($client);
        $this->loggerMock->expects($this->never())->method('critical');
        $this->loggerMock->expects($this->never())->method('warning');
        $this->loggerMock->expects($this->once())->method('execute');

        $this->assertTrue($this->model->sendPurgeRequest('tags'));
    }

    /**
     * @param string $host
     * @param int $port
     * @return Uri
     */
    private function createUri(string $host, int $port): Uri
    {
        return UriFactory::factory('')->setHost($host)->setPort($port)->setScheme('http')->setPath('/');
    }

    /**
     * @param Uri $uri
     * @param string $tags
     * @param string|null $error
     * @return Curl&MockObject
     */
    private function createClient(Uri $uri, string $tags, ?string $error = null): Curl
    {
        $client = $this->createMock(Curl::class);
        $client->expects($this->once())->method('setTimeout')->with(10);
        $client->expects($this->once())->method('setHeaders')->with([
            'X-Magento-Tags-Pattern' => $tags,
            'Host' => $uri->getHost()
        ]);
        $options = [CURLOPT_CUSTOMREQUEST => 'PURGE', CURLOPT_PROXY => ''];
        $client->expects($this->exactly(2))->method('setOption')->willReturnCallback(
            function ($option, $value) use (&$options): void {
                $this->assertArrayHasKey($option, $options);
                $this->assertSame($options[$option], $value);
                unset($options[$option]);
            }
        );
        $request = $client->expects($this->once())->method('get')->with((string)$uri);
        if ($error !== null) {
            $request->willThrowException(new RuntimeException($error));
        }

        return $client;
    }
}
