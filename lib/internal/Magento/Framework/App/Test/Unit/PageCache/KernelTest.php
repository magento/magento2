<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\PageCache;

use Laminas\Http\Header\CacheControl;
use Laminas\Http\Headers;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Http\ContextFactory;
use Magento\Framework\App\PageCache\Cache;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\PageCache\Kernel;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\HttpFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\PageCache\Model\Cache\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class KernelTest extends TestCase
{
    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var Cache|MockObject
     */
    protected $cacheMock;

    /**
     * @var Identifier|MockObject
     */
    protected $identifierMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    protected $responseMock;

    /**
     * @var MockObject|Type
     */
    private $fullPageCacheMock;

    /**
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    private $httpResponseMock;

    /**
     * @var ContextFactory|MockObject
     */
    private $contextFactoryMock;

    /**
     * @var HttpFactory|MockObject
     */
    private $httpFactoryMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $headersMock = $this->createMock(Headers::class);
        $this->cacheMock = $this->createMock(Cache::class);
        $this->fullPageCacheMock = $this->createMock(Type::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->httpResponseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->identifierMock = $this->createMock(Identifier::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->contextFactoryMock = $this->createPartialMock(ContextFactory::class, ['create']);
        $this->httpFactoryMock = $this->createPartialMock(HttpFactory::class, ['create']);
        $this->responseMock->expects($this->any())->method('getHeaders')->willReturn($headersMock);

        $this->kernel = new Kernel(
            $this->cacheMock,
            $this->identifierMock,
            $this->requestMock,
            $this->contextMock,
            $this->contextFactoryMock,
            $this->httpFactoryMock,
            $this->serializer
        );

        $reflection = new \ReflectionClass(Kernel::class);
        $reflectionProperty = $reflection->getProperty('fullPageCache');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->kernel, $this->fullPageCacheMock);
    }

    /**
     * @param string $id
     * @param mixed $cache
     * @param bool $isGet
     * @param bool $isHead
     *
     * @return void
     * @dataProvider dataProviderForResultWithCachedData
     */
    public function testLoadWithCachedData($id, $cache, $isGet, $isHead): void
    {
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->contextFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    'data' => ['context_data'],
                    'default' => ['context_default_data']
                ]
            )
            ->willReturn($this->contextMock);

        $this->httpFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['context' => $this->contextMock])
            ->willReturn($this->httpResponseMock);

        $this->requestMock->expects($this->once())->method('isGet')->willReturn($isGet);
        $this->requestMock->expects($this->any())->method('isHead')->willReturn($isHead);
        $this->fullPageCacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $id
        )->willReturn(
            json_encode($cache)
        );
        $this->httpResponseMock->expects($this->once())->method('setStatusCode')->with($cache['status_code']);
        $this->httpResponseMock->expects($this->once())->method('setContent')->with($cache['content']);
        $this->httpResponseMock->expects($this->once())->method('setHeader')->with(0, 'header', true);
        $this->identifierMock->expects($this->any())->method('getValue')->willReturn($id);
        $this->assertEquals($this->httpResponseMock, $this->kernel->load());
    }

    /**
     * @return array
     */
    public function dataProviderForResultWithCachedData(): array
    {
        $data = [
            'context' => [
                'data' => ['context_data'],
                'default' => ['context_default_data']
            ],
            'status_code' => 'status_code',
            'content' => 'content',
            'headers' => ['header']
        ];

        return [
            ['existing key', $data, true, false],
            ['existing key', $data, false, true]
        ];
    }

    /**
     * @param string $id
     * @param mixed $cache
     * @param bool $isGet
     * @param bool $isHead
     *
     * @return void
     * @dataProvider dataProviderForResultWithoutCachedData
     */
    public function testLoadWithoutCachedData($id, $cache, $isGet, $isHead): void
    {
        $this->requestMock->expects($this->once())->method('isGet')->willReturn($isGet);
        $this->requestMock->expects($this->any())->method('isHead')->willReturn($isHead);
        $this->fullPageCacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $id
        )->willReturn(
            json_encode($cache)
        );
        $this->identifierMock->expects($this->any())->method('getValue')->willReturn($id);
        $this->assertFalse($this->kernel->load());
    }

    /**
     * @return array
     */
    public function dataProviderForResultWithoutCachedData(): array
    {
        return [
            ['existing key', [], false, false],
            ['non existing key', false, true, false],
            ['non existing key', false, false, false]
        ];
    }

    /**
     * @param $httpCode
     *
     * @return void
     * @dataProvider testProcessSaveCacheDataProvider
     */
    public function testProcessSaveCache($httpCode): void
    {
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $cacheControlHeader = CacheControl::fromString(
            'Cache-Control: public, max-age=100, s-maxage=100'
        );

        $this->responseMock
            ->method('getHeader')
            ->withConsecutive(['Cache-Control'], ['X-Magento-Tags'])
            ->willReturn($cacheControlHeader, null);
        $this->responseMock->expects(
            $this->any()
        )->method(
            'getHttpResponseCode'
        )->willReturn($httpCode);
        $this->requestMock->expects($this->once())
            ->method('isGet')
            ->willReturn(true);
        $this->responseMock->expects($this->once())
            ->method('setNoCacheHeaders');
        $this->responseMock
            ->method('clearHeader')
            ->withConsecutive(['Set-Cookie'], ['X-Magento-Tags']);
        $this->fullPageCacheMock->expects($this->once())
            ->method('save');
        $this->kernel->process($this->responseMock);
    }

    /**
     * @return array
     */
    public function testProcessSaveCacheDataProvider(): array
    {
        return [
            [200],
            [404]
        ];
    }

    /**
     * @param string $cacheControlHeader
     * @param int $httpCode
     * @param bool $isGet
     * @param bool $overrideHeaders
     *
     * @return void
     * @dataProvider processNotSaveCacheProvider
     */
    public function testProcessNotSaveCache($cacheControlHeader, $httpCode, $isGet, $overrideHeaders): void
    {
        $header = CacheControl::fromString("Cache-Control: $cacheControlHeader");
        $this->responseMock->expects(
            $this->once()
        )->method(
            'getHeader'
        )->with(
            'Cache-Control'
        )->willReturn(
            $header
        );
        $this->responseMock->expects($this->any())->method('getHttpResponseCode')->willReturn($httpCode);
        $this->requestMock->expects($this->any())->method('isGet')->willReturn($isGet);
        if ($overrideHeaders) {
            $this->responseMock->expects($this->once())->method('setNoCacheHeaders');
        }
        $this->fullPageCacheMock->expects($this->never())->method('save');
        $this->kernel->process($this->responseMock);
    }

    /**
     * @return array
     */
    public function processNotSaveCacheProvider(): array
    {
        return [
            ['private, max-age=100', 200, true, false],
            ['private, max-age=100', 200, false, false],
            ['private, max-age=100', 500, true, false],
            ['no-store, no-cache, must-revalidate, max-age=0', 200, true, false],
            ['no-store, no-cache, must-revalidate, max-age=0', 200, false, false],
            ['no-store, no-cache, must-revalidate, max-age=0', 404, true, false],
            ['no-store, no-cache, must-revalidate, max-age=0', 500, true, false],
            ['public, max-age=100, s-maxage=100', 500, true, true],
            ['public, max-age=100, s-maxage=100', 200, false, true]
        ];
    }
}
