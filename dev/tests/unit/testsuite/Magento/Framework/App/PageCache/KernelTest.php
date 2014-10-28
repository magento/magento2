<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->cacheMock = $this->getMock('Magento\Framework\App\PageCache\Cache', array(), array(), '', false);
        $this->identifierMock =
            $this->getMock('Magento\Framework\App\PageCache\Identifier', array(), array(), '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
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
        $data = array(1, 2, 3);
        return array(
            array($data, 'existing key', $data, true, false),
            array($data, 'existing key', $data, false, true),
            array(
                new \Magento\Framework\Object($data),
                'existing key',
                new \Magento\Framework\Object($data),
                true,
                false
            ),
            array(false, 'existing key', $data, false, false),
            array(false, 'non existing key', false, true, false),
            array(false, 'non existing key', false, false, false)
        );
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
            $this->returnValue(array('value' => $cacheControlHeader))
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
            $this->returnValue(array('value' => $cacheControlHeader))
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
        return array(
            array('private, max-age=100', 200, true, false),
            array('private, max-age=100', 200, false, false),
            array('private, max-age=100', 404, true, false),
            array('private, max-age=100', 500, true, false),
            array('no-store, no-cache, must-revalidate, max-age=0', 200, true, false),
            array('no-store, no-cache, must-revalidate, max-age=0', 200, false, false),
            array('no-store, no-cache, must-revalidate, max-age=0', 404, true, false),
            array('no-store, no-cache, must-revalidate, max-age=0', 500, true, false),
            array('public, max-age=100, s-maxage=100', 404, true, true),
            array('public, max-age=100, s-maxage=100', 500, true, true),
            array('public, max-age=100, s-maxage=100', 200, false, true)
        );
    }
}
