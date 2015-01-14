<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\ProductList;

use Magento\TestFramework\Helper\ObjectManager;

class ToolbarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Toolbar
     */
    protected $toolbarModel;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * @var \Magento\Framework\App\Request\Http |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->cookieManagerMock = $this->getMock('Magento\Framework\Stdlib\CookieManagerInterface');
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->toolbarModel = (new ObjectManager($this))->getObject(
            'Magento\Catalog\Model\Product\ProductList\Toolbar',
            [
                'cookieManager' => $this->cookieManagerMock,
                'request' => $this->requestMock,
            ]
        );
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetOrder($param)
    {
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(Toolbar::ORDER_COOKIE_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getOrder());
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetDirection($param)
    {
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(Toolbar::DIRECTION_COOKIE_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getDirection());
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetMode($param)
    {
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(Toolbar::MODE_COOKIE_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getMode());
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetLimit($param)
    {
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(Toolbar::LIMIT_COOKIE_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getLimit());
    }

    /**
     * @dataProvider intParamProvider
     * @param $param
     */
    public function testGetCurrentPage($param)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::PAGE_PARM_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getCurrentPage());
    }

    public function testGetCurrentPageNoParam()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::PAGE_PARM_NAME)
            ->will($this->returnValue(false));
        $this->assertEquals(1, $this->toolbarModel->getCurrentPage());
    }

    public function stringParamProvider()
    {
        return [
            ['stringParam']
        ];
    }

    public function intParamProvider()
    {
        return [
            ['2'],
            [3]
        ];
    }
}
