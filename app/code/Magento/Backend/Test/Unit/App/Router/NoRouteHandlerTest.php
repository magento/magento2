<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\App\Router;

class NoRouteHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_routeConfigMock;

    /**
     * @var \Magento\Backend\App\Router\NoRouteHandler
     */
    protected $_model;

    protected function setUp()
    {
        $this->_requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->_routeConfigMock = $this->getMock(\Magento\Framework\App\Route\ConfigInterface::class);
        $this->_helperMock = $this->getMock(\Magento\Backend\Helper\Data::class, [], [], '', false);
        $this->_helperMock->expects($this->any())->method('getAreaFrontName')->will($this->returnValue('backend'));
        $this->_model = new \Magento\Backend\App\Router\NoRouteHandler($this->_helperMock, $this->_routeConfigMock);
    }

    /**
     * @covers \Magento\Backend\App\Router\NoRouteHandler::process
     */
    public function testProcessWithBackendAreaFrontName()
    {
        $this->_routeConfigMock
            ->expects($this->once())
            ->method('getRouteFrontName')
            ->with('adminhtml')
            ->will($this->returnValue('admin'));

        $this->_requestMock
            ->expects($this->once())
            ->method('getPathInfo')
            ->will($this->returnValue('backend/admin/custom'));

        $this->_requestMock->expects(
            $this->once()
        )->method(
            'setModuleName'
        )->with(
            'admin'
        )->will(
            $this->returnValue($this->_requestMock)
        );

        $this->_requestMock->expects(
            $this->once()
        )->method(
            'setControllerName'
        )->with(
            'noroute'
        )->will(
            $this->returnValue($this->_requestMock)
        );

        $this->_requestMock->expects(
            $this->once()
        )->method(
            'setActionName'
        )->with(
            'index'
        )->will(
            $this->returnValue($this->_requestMock)
        );

        $this->assertEquals(true, $this->_model->process($this->_requestMock));
    }

    /**
     * @covers \Magento\Backend\App\Router\NoRouteHandler::process
     */
    public function testProcessWithoutAreaFrontName()
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getPathInfo'
        )->will(
            $this->returnValue('module/controller/action')
        );

        $this->_requestMock->expects($this->never())->method('setModuleName');

        $this->_requestMock->expects($this->never())->method('setControllerName');

        $this->_requestMock->expects($this->never())->method('setActionName');

        $this->assertEquals(false, $this->_model->process($this->_requestMock));
    }
}
