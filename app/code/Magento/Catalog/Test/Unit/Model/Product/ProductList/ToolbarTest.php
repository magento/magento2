<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\ProductList;

use \Magento\Catalog\Model\Product\ProductList\Toolbar;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ToolbarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Toolbar
     */
    protected $toolbarModel;

    /**
     * @var \Magento\Framework\App\Request\Http |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->toolbarModel = (new ObjectManager($this))->getObject(
            \Magento\Catalog\Model\Product\ProductList\Toolbar::class,
            [
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
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::ORDER_PARAM_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getOrder());
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetDirection($param)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::DIRECTION_PARAM_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getDirection());
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetMode($param)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::MODE_PARAM_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getMode());
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetLimit($param)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::LIMIT_PARAM_NAME)
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
