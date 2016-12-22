<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\PageCache;

use \Magento\Framework\App\PageCache\Kernel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class KernelTest extends \PHPUnit_Framework_TestCase
{
    /** @var Kernel */
    protected $kernel;

    /** @var \Magento\Framework\App\PageCache\Cache|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheMock;

    /** @var \Magento\Framework\App\PageCache\Identifier|\PHPUnit_Framework_MockObject_MockObject */
    protected $identifierMock;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $responseMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Cache\Type */
    private $fullPageCacheMock;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    private $httpResponseMock;

    /** @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $serializer;

    /** @var \Magento\Framework\App\ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $contextMock;

    /**
     * Setup
     */
    protected function setUp()
    {
        $headersMock = $this->getMock(\Zend\Http\Headers::class, [], [], '', false);
        $this->cacheMock = $this->getMock(\Magento\Framework\App\PageCache\Cache::class, [], [], '', false);
        $this->fullPageCacheMock = $this->getMock(\Magento\PageCache\Model\Cache\Type::class, [], [], '', false);
        $this->contextMock = $this->getMock(\Magento\Framework\App\Http\Context::class, [], [], '', false);
        $this->httpResponseMock = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->objectManager = $this->getMock(\Magento\Framework\App\ObjectManager::class, [], [], '', false);
        $this->identifierMock = $this->getMock(\Magento\Framework\App\PageCache\Identifier::class, [], [], '', false);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->serializer = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class, [], [], '', false);
        $this->responseMock = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->responseMock->expects($this->any())->method('getHeaders')->willReturn($headersMock);

        $this->kernel = new Kernel($this->cacheMock, $this->identifierMock, $this->requestMock, $this->serializer);

        $reflection = new \ReflectionClass(\Magento\Framework\App\PageCache\Kernel::class);
        $reflectionProperty = $reflection->getProperty('fullPageCache');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->kernel, $this->fullPageCacheMock);
        $reflectionProperty = $reflection->getProperty('objectManager');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->kernel, $this->objectManager);
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

        $this->objectManager->method('create')
            ->will($this->returnValueMap([
                [
                    \Magento\Framework\App\Http\Context::class,
                    [
                        'data' => ['context_data'],
                        'default' => ['context_default_data']
                    ],
                    $this->contextMock
                ],
                [
                    \Magento\Framework\App\Response\Http::class,
                    ['context' => $this->contextMock],
                    $this->httpResponseMock]
            ]));
        $this->requestMock->expects($this->once())->method('isGet')->will($this->returnValue($isGet));
        $this->requestMock->expects($this->any())->method('isHead')->will($this->returnValue($isHead));
        $this->fullPageCacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->equalTo($id)
        )->will(
            $this->returnValue(json_encode($cache))
        );
        $this->httpResponseMock->expects($this->once())->method('setStatusCode')->with($cache['status_code']);
        $this->httpResponseMock->expects($this->once())->method('setContent')->with($cache['content']);
        $this->httpResponseMock->expects($this->once())->method('setHeader')->with(0, 'header', true);
        $this->identifierMock->expects($this->any())->method('getValue')->will($this->returnValue($id));
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
        $this->requestMock->expects($this->once())->method('isGet')->will($this->returnValue($isGet));
        $this->requestMock->expects($this->any())->method('isHead')->will($this->returnValue($isHead));
        $this->fullPageCacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->equalTo($id)
        )->will(
            $this->returnValue(json_encode($cache))
        );
        $this->identifierMock->expects($this->any())->method('getValue')->will($this->returnValue($id));
        $this->assertEquals(false, $this->kernel->load());
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
        $cacheTypeMock = $this->getMock(\Magento\PageCache\Model\Cache\Type::class, [], [], '', false);
        $this->objectManager->method('get')
            ->will($this->returnValueMap([
                [\Magento\Framework\App\Http\Context::class, $this->contextMock],
                [\Magento\PageCache\Model\Cache\Type::class, $cacheTypeMock],
            ]));
        $cacheControlHeader = \Zend\Http\Header\CacheControl::fromString(
            'Cache-Control: public, max-age=100, s-maxage=100'
        );

        $this->responseMock->expects(
            $this->at(0)
        )->method(
            'getHeader'
        )->with(
            'Cache-Control'
        )->will(
            $this->returnValue($cacheControlHeader)
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
        )->will(
            $this->returnValue($header)
        );
        $this->responseMock->expects($this->any())->method('getHttpResponseCode')->will($this->returnValue($httpCode));
        $this->requestMock->expects($this->any())->method('isGet')->will($this->returnValue($isGet));
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
