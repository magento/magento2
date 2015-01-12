<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class VoidTest
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 */
class VoidTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\UpdateQty
     */
    protected $controller;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->titleMock = $this->getMockBuilder('Magento\Framework\App\Action\Title')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->actionFlagMock = $this->getMockBuilder('Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->helperMock = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));
        $contextMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($this->titleMock));
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->will($this->returnValue($this->actionFlagMock));
        $contextMock->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $contextMock->expects($this->any())
            ->method('getHelper')
            ->will($this->returnValue($this->helperMock));

        $this->controller = $objectManager->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Invoice\Void',
            [
                'context' => $contextMock
            ]
        );
    }

    public function testExecute()
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['setIsInProcess', '__wakeup'])
            ->getMock();

        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('load')
            ->with($invoiceId)
            ->willReturnSelf();
        $invoiceMock->expects($this->once())
            ->method('void');
        $invoiceMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));

        $transactionMock = $this->getMockBuilder('Magento\Framework\DB\Transaction')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $transactionMock->expects($this->at(0))
            ->method('addObject')
            ->with($invoiceMock)
            ->will($this->returnSelf());
        $transactionMock->expects($this->at(1))
            ->method('addObject')
            ->with($orderMock)
            ->will($this->returnSelf());
        $transactionMock->expects($this->at(2))
            ->method('save');

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Model\Order\Invoice')
            ->willReturn($invoiceMock);
        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with('Magento\Framework\DB\Transaction')
            ->will($this->returnValue($transactionMock));

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with('The invoice has been voided.');

        $this->controller->execute();
    }

    public function testExecuteNoInvoice()
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('load')
            ->willReturn(null);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Sales\Model\Order\Invoice')
            ->willReturn($invoiceMock);

        $this->messageManagerMock->expects($this->never())
            ->method('addError');
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');

        $this->controller->execute();
    }

    public function testExecuteModelException()
    {
        $invoiceId = 2;
        $message = 'test message';
        $e = new \Magento\Framework\Model\Exception($message);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('load')
            ->with($invoiceId)
            ->willReturnSelf();
        $invoiceMock->expects($this->once())
            ->method('void')
            ->will($this->throwException($e));

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Sales\Model\Order\Invoice')
            ->willReturn($invoiceMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addError');
        $this->controller->execute();
    }
}
