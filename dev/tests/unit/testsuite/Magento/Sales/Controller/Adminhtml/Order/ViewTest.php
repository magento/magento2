<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

/**
 * Magento Adminhtml Order Controller Test
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * Mock for \Magento\Sales\Model\Order
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderMock;

    /**
     * Mock for \Magento\Framework\Message
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageMock;

    /**
     * Mock for \Magento\Sales\Adminhtml\Controller\Order
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_controllerMock;

    protected $viewMock;

    /**
     * setup mocks for all functions
     */
    public function setUp()
    {
        $this->_objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_orderMock = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getRealOrderId'])
            ->getMock();

        $this->_messageMock = $this->getMockBuilder('\Magento\Framework\Message')
            ->disableOriginalConstructor()
            ->setMethods(['addError'])
            ->getMock();

        $this->viewMock = $this->getMockForAbstractClass('\Magento\Framework\App\ViewInterface');

        /**
         * @TODO:
         *  - Methods of object under test MUST NOT be mocked
         *  - Protected properties MUST NOT be set from outside, inject via context passed to constructor instead
         */
        $this->_controllerMock = $this->getMockBuilder('\Magento\Sales\Controller\Adminhtml\Order\View')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', '_initOrder', '_initAction', '__', 'renderLayout', '_redirect'])
            ->getMock();
        $this->_controllerMock->expects($this->any())->method('__')->will($this->returnArgument(0));

        $reflectionProperty = new \ReflectionProperty('\Magento\Sales\Controller\Adminhtml\Order', '_view');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_controllerMock, $this->viewMock);
        $reflectionProperty->setAccessible(false);

        $reflectionProperty = new \ReflectionProperty('\Magento\Sales\Controller\Adminhtml\Order', 'messageManager');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_controllerMock, $this->_messageMock);
        $reflectionProperty->setAccessible(false);
    }

    /**
     * This function checks if the error is added to session in case of ACTION_FLAG_PRODUCTS_PERMISSION_DENIED
     * is set in Sales Order model
     */
    public function testViewActionWithError()
    {
        $msg = 'You need more permissions to view this item.';
        $this->_messageMock->expects($this->once())->method('addError')->with($this->equalTo($msg));
        $this->_controllerMock->expects($this->once())
            ->method('_initOrder')
            ->will($this->returnValue($this->_orderMock));
        $this->_controllerMock->expects($this->once())
            ->method('_initAction')
            ->will($this->throwException(new \Magento\Framework\App\Action\Exception($msg)));
        $this->_orderMock->expects($this->never())->method('getRealOrderId');

        $this->_controllerMock->execute();
    }

    /**
     * This function checks if the error is added to session in case of ACTION_FLAG_PRODUCTS_PERMISSION_DENIED
     * is not set in Sales Order model
     */
    public function testViewActionWithoutError()
    {
        $realOrderId = 1;
        $this->_controllerMock->expects($this->once())
            ->method('_initOrder')
            ->will($this->returnValue($this->_orderMock));
        $this->_messageMock->expects($this->never())->method('addError');
        $this->_orderMock->expects($this->once())->method('getRealOrderId')->will($this->returnValue($realOrderId));

        $pageTitle = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfig = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getTitle'])
            ->getMock();
        $pageConfig->expects($this->atLeastOnce())->method('getTitle')->willReturn($pageTitle);
        $resultPage = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->setMethods(['getConfig'])
            ->getMock();
        $resultPage->expects($this->atLeastOnce())->method('getConfig')->willReturn($pageConfig);
        $this->viewMock->expects($this->atLeastOnce())->method('getPage')->willReturn($resultPage);

        $pageTitle->expects($this->at(0))->method('prepend')->with('Orders')->willReturnSelf();
        $pageTitle->expects($this->at(1))->method('prepend')->with('#' . $realOrderId)->willReturnSelf();
        $this->_controllerMock->execute();
    }
}
