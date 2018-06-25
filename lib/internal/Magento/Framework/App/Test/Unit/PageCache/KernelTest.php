<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\PageCache;

use \Magento\Framework\App\PageCache\Kernel;

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

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->cacheMock = $this->getMock('Magento\Framework\App\PageCache\Cache', [], [], '', false);
        $this->fullPageCacheMock = $this->getMock('\Magento\PageCache\Model\Cache\Type', [], [], '', false);
        $this->identifierMock =
            $this->getMock('Magento\Framework\App\PageCache\Identifier', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->kernel = new Kernel($this->cacheMock, $this->identifierMock, $this->requestMock);

        $reflection = new \ReflectionClass('\Magento\Framework\App\PageCache\Kernel');
        $reflectionProperty = $reflection->getProperty('fullPageCache');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->kernel, $this->fullPageCacheMock);

        $this->responseMock = $this->getMockBuilder(
            'Magento\Framework\App\Response\Http'
        )->setMethods(
            ['getHeader', 'getHttpResponseCode', 'setNoCacheHeaders', 'clearHeader', '__wakeup']
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * @dataProvider loadProvider
     * @param mixed $expected
     * @param string $id
     * @param mixed $cache
     * @param bool $isGet
     * @param bool $isHead
     */
    public function testLoad($expected, $id, $cache, $isGet, $isHead)
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
            $this->returnValue(serialize($cache))
        );
        $this->identifierMock->expects($this->any())->method('getValue')->will($this->returnValue($id));
        $this->assertEquals($expected, $this->kernel->load());
    }

    /**
     * @return array
     */
    public function loadProvider()
    {
        $data = [1, 2, 3];
        return [
            [$data, 'existing key', $data, true, false],
            [$data, 'existing key', $data, false, true],
            [
                new \Magento\Framework\DataObject($data),
                'existing key',
                new \Magento\Framework\DataObject($data),
                true,
                false
            ],
            [false, 'existing key', $data, false, false],
            [false, 'non existing key', false, true, false],
            [false, 'non existing key', false, false, false]
        ];
    }

    /**
     * @param $httpCode
     * @dataProvider testProcessSaveCacheDataProvider
     */
    public function testProcessSaveCache($httpCode, $at)
    {
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
