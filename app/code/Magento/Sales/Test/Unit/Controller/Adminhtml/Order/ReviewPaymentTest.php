<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

/**
 * Class PaymentTest
 *
 * @package Magento\Sales\Controller\Adminhtml\Order
 */
class ReviewPaymentTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Controller\Adminhtml\Order\ReviewPayment | \PHPUnit_Framework_MockObject_MockObject */
    protected $reviewPayment;

    /** @var  \Magento\Backend\App\Action\Context| \PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    /** @var  \Magento\Sales\Model\Order |\PHPUnit_Framework_MockObject_MockObject */
    protected $orderMock;

    /** @var  \Magento\Backend\Model\View\Result\RedirectFactory | \PHPUnit_Framework_MockObject_MockObject*/
    protected $resultRedirectFactoryMock;

    /** @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirectMock;

    /**@var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var  \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject */
    protected $paymentMock;

    /** @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManagerMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [
                'getRequest',
                'getResponse',
                'getMessageManager',
                'getRedirect',
                'getObjectManager',
                'getSession',
                'getActionFlag',
                'getHelper',
                'getResultRedirectFactory'
            ],
            [],
            '',
            false
        );

        $this->messageManagerMock = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addSuccess', 'addError'],
            [],
            '',
            false
        );

        $this->resultRedirectFactoryMock = $this->getMock(
            'Magento\Backend\Model\View\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->paymentMock = $this->getMock(
            'Magento\Sales\Model\Order\Payment',
            ['update', 'getIsTransactionApproved'],
            [],
            '',
            false
        );

        $this->orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            ['load', 'getId', 'getPayment', 'save'],
            [],
            '',
            false
        );

        $this->objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            ['create'],
            [],
            '',
            false
        );


        $this->resultRedirectMock = $this->getMock(
            'Magento\Backend\Model\View\Result\Redirect',
            ['setPath'],
            [],
            '',
            false
        );

        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()->getMock();

        $this->contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->reviewPayment = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\ReviewPayment',
            [
                'context' => $this->contextMock
            ]
        );
    }

    public function testExecuteUpdateAction()
    {
        $orderId = 30;
        $action = 'update';

        $this->requestMock->expects($this->at(0))->method('getParam')->with('order_id')->willReturn($orderId);
        $this->requestMock->expects($this->at(1))->method('getParam')->with('action')->willReturn($action);

        $this->resultRedirectFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->objectManagerMock->expects($this->once())->method('create')->with('Magento\Sales\Model\Order')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())->method('load')->with($orderId)->willReturn($this->orderMock);
        $this->orderMock->expects($this->any())->method('getId')->willReturn($orderId);
        $this->orderMock->expects($this->any())->method('getPayment')->willReturn($this->paymentMock);
        $this->orderMock->expects($this->once())->method('save')->willReturnSelf();

        $this->paymentMock->expects($this->once())->method('update');
        $this->paymentMock->expects($this->any())->method('getIsTransactionApproved')->willReturn(true);
        
        $this->messageManagerMock->expects($this->once())->method('addSuccess');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/order/view')
            ->willReturnSelf();

        $result = $this->reviewPayment->execute();
        $this->assertEquals($this->resultRedirectMock, $result);
    }
}
