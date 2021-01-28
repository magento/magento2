<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\PageCache;

use \Magento\Framework\App\PageCache\Kernel;
use \Magento\Framework\App\Http\ContextFactory;
use \Magento\Framework\App\Response\HttpFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class KernelTest extends \PHPUnit\Framework\TestCase
{
    /** @var Kernel */
    protected $kernel;

    /** @var \Magento\Framework\App\PageCache\Cache|\PHPUnit\Framework\MockObject\MockObject */
    protected $cacheMock;

    /** @var \Magento\Framework\App\PageCache\Identifier|\PHPUnit\Framework\MockObject\MockObject */
    protected $identifierMock;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject */
    protected $responseMock;

    /** @var  \PHPUnit\Framework\MockObject\MockObject|\Magento\PageCache\Model\Cache\Type */
    private $fullPageCacheMock;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject */
    private $httpResponseMock;

    /** @var ContextFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $contextFactoryMock;

    /** @var HttpFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $httpFactoryMock;

    /** @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $serializer;

    /** @var \Magento\Framework\App\Http\Context|\PHPUnit\Framework\MockObject\MockObject */
    private $contextMock;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $headersMock = $this->createMock(\Zend\Http\Headers::class);
        $this->cacheMock = $this->createMock(\Magento\Framework\App\PageCache\Cache::class);
        $this->fullPageCacheMock = $this->createMock(\Magento\PageCache\Model\Cache\Type::class);
        $this->contextMock = $this->createMock(\Magento\Framework\App\Http\Context::class);
        $this->httpResponseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->identifierMock = $this->createMock(\Magento\Framework\App\PageCache\Identifier::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->serializer = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
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

        $reflection = new \ReflectionClass(\Magento\Framework\App\PageCache\Kernel::class);
        $reflectionProperty = $reflection->getProperty('fullPageCache');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->kernel, $this->fullPageCacheMock);
    }

    /**
     * @dataProvider dataProviderForResultWithCachedData
     * @param string $id
     * @param mixed $cache
     * @param bool $isGet
     * @param bool $isHead
     */
    public function testLoadWithCachedData($id, $cache, $isGet, $isHead)
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
            $this->equalTo($id)
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
    public function dataProviderForResultWithCachedData()
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
            ['existing key', $data, false, true],
        ];
    }

    /**
     * @dataProvider dataProviderForResultWithoutCachedData
     * @param string $id
     * @param mixed $cache
     * @param bool $isGet
     * @param bool $isHead
     */
    public function testLoadWithoutCachedData($id, $cache, $isGet, $isHead)
    {
        $this->requestMock->expects($this->once())->method('isGet')->willReturn($isGet);
        $this->requestMock->expects($this->any())->method('isHead')->willReturn($isHead);
        $this->fullPageCacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->equalTo($id)
        )->willReturn(
            json_encode($cache)
        );
        $this->identifierMock->expects($this->any())->method('getValue')->willReturn($id);
        $this->assertFalse($this->kernel->load());
    }

    /**
     * @return array
     */
    public function dataProviderForResultWithoutCachedData()
    {
        return [
            ['existing key', [], false, false],
            ['non existing key', false, true, false],
            ['non existing key', false, false, false]
        ];
    }

    /**
     * @param $httpCode
     * @dataProvider testProcessSaveCacheDataProvider
     */
    public function testProcessSaveCache($httpCode, $at)
    {
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $cacheControlHeader = \Zend\Http\Header\CacheControl::fromString(
            'Cache-Control: public, max-age=100, s-maxage=100'
        );

        $this->responseMock->expects(
            $this->at(0)
        )->method(
            'getHeader'
        )->with(
            'Cache-Control'
        )->willReturn(
            $cacheControlHeader
        );
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
        $this->responseMock->expects($this->at($at[0]))
            ->method('getHeader')
            ->with('X-Magento-Tags');
        $this->responseMock->expects($this->at($at[1]))
            ->method('clearHeader')
            ->with($this->equalTo('Set-Cookie'));
        $this->responseMock->expects($this->at($at[2]))
            ->method('clearHeader')
            ->with($this->equalTo('X-Magento-Tags'));
        $this->fullPageCacheMock->expects($this->once())
            ->method('save');
        $this->kernel->process($this->responseMock);
    }

    /**
     * @return array
     */
    public function testProcessSaveCacheDataProvider()
    {
        return [
            [200, [3, 4, 5]],
            [404, [4, 5, 6]]
        ];
    }

    /**
     * @dataProvider processNotSaveCacheProvider
     * @param string $cacheControlHeader
     * @param int $httpCode
     * @param bool $isGet
     * @param bool $overrideHeaders
     */
    public function testProcessNotSaveCache($cacheControlHeader, $httpCode, $isGet, $overrideHeaders)
    {
        $header = \Zend\Http\Header\CacheControl::fromString("Cache-Control: $cacheControlHeader");
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
    public function processNotSaveCacheProvider()
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
