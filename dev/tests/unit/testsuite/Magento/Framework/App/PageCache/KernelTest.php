<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

class KernelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var \Magento\Framework\App\PageCache\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \Magento\Framework\App\PageCache\Identifier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $identifierMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->cacheMock = $this->getMock('Magento\Framework\App\PageCache\Cache', [], [], '', false);
        $this->identifierMock =
            $this->getMock('Magento\Framework\App\PageCache\Identifier', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->kernel = new Kernel($this->cacheMock, $this->identifierMock, $this->requestMock);
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
        $this->cacheMock->expects(
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
                new \Magento\Framework\Object($data),
                'existing key',
                new \Magento\Framework\Object($data),
                true,
                false
            ],
            [false, 'existing key', $data, false, false],
            [false, 'non existing key', false, true, false],
            [false, 'non existing key', false, false, false]
        ];
    }

    public function testProcessSaveCache()
    {
        $cacheControlHeader = 'public, max-age=100, s-maxage=100';
        $httpCode = 200;

        $this->responseMock->expects(
            $this->at(0)
        )->method(
            'getHeader'
        )->with(
            'Cache-Control'
        )->will(
            $this->returnValue(['value' => $cacheControlHeader])
        );
        $this->responseMock->expects(
            $this->once()
        )->method(
            'getHttpResponseCode'
        )->will(
            $this->returnValue($httpCode)
        );
        $this->requestMock->expects($this->once())->method('isGet')->will($this->returnValue(true));
        $this->responseMock->expects($this->once())->method('setNoCacheHeaders');
        $this->responseMock->expects($this->at(3))->method('getHeader')->with('X-Magento-Tags');
        $this->responseMock->expects($this->at(4))->method('clearHeader')->with($this->equalTo('Set-Cookie'));
        $this->responseMock->expects($this->at(5))->method('clearHeader')->with($this->equalTo('X-Magento-Tags'));
        $this->cacheMock->expects($this->once())->method('save');
        $this->kernel->process($this->responseMock);
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
        $this->responseMock->expects(
            $this->once()
        )->method(
            'getHeader'
        )->with(
            'Cache-Control'
        )->will(
            $this->returnValue(['value' => $cacheControlHeader])
        );
        $this->responseMock->expects($this->any())->method('getHttpResponseCode')->will($this->returnValue($httpCode));
        $this->requestMock->expects($this->any())->method('isGet')->will($this->returnValue($isGet));
        if ($overrideHeaders) {
            $this->responseMock->expects($this->once())->method('setNoCacheHeaders');
        }
        $this->cacheMock->expects($this->never())->method('save');
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
            ['private, max-age=100', 404, true, false],
            ['private, max-age=100', 500, true, false],
            ['no-store, no-cache, must-revalidate, max-age=0', 200, true, false],
            ['no-store, no-cache, must-revalidate, max-age=0', 200, false, false],
            ['no-store, no-cache, must-revalidate, max-age=0', 404, true, false],
            ['no-store, no-cache, must-revalidate, max-age=0', 500, true, false],
            ['public, max-age=100, s-maxage=100', 404, true, true],
            ['public, max-age=100, s-maxage=100', 500, true, true],
            ['public, max-age=100, s-maxage=100', 200, false, true]
        ];
    }
}
