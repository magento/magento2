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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Http\Handler;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\HTTP\Handler\Composite
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handlerFactoryMock;

    protected function setUp()
    {
        $handlers = array(
            'app' => array(
                'sortOrder' => 50,
                'class' => 'Class_App_Handler',
            ),
            'fpc' => array(
                'sortOrder' => 20,
                'class' => 'Class_Fpc_Handler',
            ),
        );
        $this->_requestMock = $this->getMock('Zend_Controller_Request_Http', array(), array(), '', false);
        $this->_responseMock = $this->getMock('Zend_Controller_Response_Http', array(), array(), '', false);
        $this->_handlerFactoryMock = $this->getMock('Magento\HTTP\HandlerFactory', array(), array(), '', false, false);
        $this->_handlerMock = $this->getMock('Magento\HTTP\HandlerInterface', array(), array(), '', false, false);
        $this->_model = new \Magento\HTTP\Handler\Composite($this->_handlerFactoryMock, $handlers);
    }

    protected function tearDown()
    {
        unset($this->_requestMock);
        unset($this->_responseMock);
        unset($this->_handlerFactoryMock);
        unset($this->_model);
    }

    public function testHandleBreaksCycleIfRequestIsDispatched()
    {
        $this->_handlerFactoryMock->expects($this->once())
            ->method('create')->with('Class_Fpc_Handler')->will($this->returnValue($this->_handlerMock));
        $this->_handlerMock->expects($this->once())
            ->method('handle')->with($this->_requestMock, $this->_responseMock);
        $this->_requestMock->expects($this->once())->method('isDispatched')->will($this->returnValue(true));

        $this->_model->handle($this->_requestMock, $this->_responseMock);
    }

    public function testSorting()
    {
        $handlers = array(
            'app' => array(
                'sortOrder' => 50,
                'class' => 'Class_App_Handler',
            ),
            'fpc' => array(
                'sortOrder' => 20,
                'class' => 'Class_Fpc_Handler',
            ),
        );

        $model = new \Magento\HTTP\Handler\Composite($this->_handlerFactoryMock, $handlers);

        $this->_handlerMock->expects($this->exactly(2))->method('handle')
            ->with($this->_requestMock, $this->_responseMock);

        $this->_handlerFactoryMock->expects($this->at(0))
            ->method('create')
            ->with('Class_Fpc_Handler')
            ->will($this->returnValue($this->_handlerMock));

        $this->_handlerFactoryMock->expects($this->at(1))
            ->method('create')
            ->with('Class_App_Handler')
            ->will($this->returnValue($this->_handlerMock));

        $model->handle($this->_requestMock, $this->_responseMock);
    }
}
