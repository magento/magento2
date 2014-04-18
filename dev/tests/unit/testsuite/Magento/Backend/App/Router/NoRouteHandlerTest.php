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
namespace Magento\Backend\App\Router;

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
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->_routeConfigMock = $this->getMock('\Magento\Framework\App\Route\ConfigInterface');
        $this->_helperMock = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false);
        $this->_helperMock->expects($this->any())->method('getAreaFrontName')->will($this->returnValue('backend'));
        $this->_model = new \Magento\Backend\App\Router\NoRouteHandler($this->_helperMock, $this->_routeConfigMock);
    }

    /**
     * @covers Magento\Backend\App\Router\NoRouteHandler::process
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
     * @covers Magento\Backend\App\Router\NoRouteHandler::process
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
