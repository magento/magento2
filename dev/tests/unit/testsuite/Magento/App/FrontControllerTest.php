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
namespace Magento\App;

class FrontControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\FrontController
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_routerList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_router;

    protected function setUp()
    {
        $this->_request = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $this->_router = $this->getMock('Magento\App\Router\AbstractRouter', array('match'), array(), '', false);
        $this->_routerList = $this->getMock('Magento\App\RouterList', array(), array(), '', false);
        $this->_routerList->expects($this->any())->method('getIterator')->will($this->returnValue($this->_routerList));
        $this->_model = new \Magento\App\FrontController($this->_routerList);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage  Front controller reached 100 router match iterations
     */
    public function testDispatchThrowException()
    {
        $this->_request->expects($this->any())->method('isDispatched')->will($this->returnValue(false));
        $this->_model->dispatch($this->_request);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage  Front controller reached 100 router match iterations
     */
    public function testWhenDispatchedActionInterface()
    {
        $this->_request->expects($this->any())->method('isDispatched')->will($this->returnValue(false));
        $this->_routerList->expects($this->atLeastOnce())->method('valid')->will($this->returnValue(true));
        $this->_routerList->expects($this->atLeastOnce())->method('current')->will($this->returnValue($this->_router));
        $controllerInstance = $this->getMock('Magento\App\ActionInterface');
        $response = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
        $controllerInstance->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $this->_router->expects($this->atLeastOnce())->method('match')->will($this->returnValue($controllerInstance));
        $controllerInstance->expects($this->any())->method('dispatch')->with($this->_request);
        $this->_model->dispatch($this->_request);
    }
}
