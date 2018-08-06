<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

/**
 * Class PaymentTest
 *
 * @package Magento\Sales\Controller\Adminhtml\Order
 */
class ReviewPaymentTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Sales\Controller\Adminhtml\Order\ReviewPayment | \PHPUnit_Framework_MockObject_MockObject */
    protected $reviewPayment;

    /** @var  \Magento\Backend\App\Action\Context| \PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var  \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject */
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

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderManagementMock;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    protected function setUp()
    {
        $this->contextMock = $this->createPartialMock(\Magento\Backend\App\Action\Context::class, [
                'getRequest',
                'getResponse',
                'getMessageManager',
                'getRedirect',
                'getObjectManager',
                'getSession',
                'getActionFlag',
                'getHelper',
                'getResultRedirectFactory'
            ]);
        $this->orderManagementMock = $this->getMockBuilder(\Magento\Sales\Api\OrderManagementInterface::class)
            ->getMockForAbstractClass();
        $this->orderRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->setMethods(['getPayment'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->createPartialMock(
            \Magento\Framework\Message\Manager::class,
            ['addSuccess', 'addError']
        );

        $this->resultRedirectFactoryMock = $this->createPartialMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create']
        );

        $this->paymentMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Payment::class,
            ['update', 'getIsTransactionApproved']
        );

        $this->resultRedirectMock = $this->createPartialMock(
            \Magento\Backend\Model\View\Result\Redirect::class,
            ['setPath']
        );

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()->getMock();
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->reviewPayment = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\ReviewPayment::class,
            [
                'context' => $this->contextMock,
                'orderManager' => $this->orderManagementMock,
                'orderRepository' => $this->orderRepositoryMock
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

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->any())->method('getEntityId')->willReturn($orderId);
        $this->orderMock->expects($this->any())->method('getPayment')->willReturn($this->paymentMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturnSelf();

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
