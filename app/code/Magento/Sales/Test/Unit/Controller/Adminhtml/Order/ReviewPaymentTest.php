<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\ReviewPayment;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReviewPaymentTest extends TestCase
{
    /** @var ReviewPayment|MockObject */
    protected $reviewPayment;

    /** @var  Context|MockObject */
    protected $contextMock;

    /** @var  OrderInterface|MockObject */
    protected $orderMock;

    /** @var  RedirectFactory|MockObject*/
    protected $resultRedirectFactoryMock;

    /** @var Redirect|MockObject */
    protected $resultRedirectMock;

    /**@var \Magento\Framework\App\Request\Http|MockObject */
    protected $requestMock;

    /** @var  Payment|MockObject */
    protected $paymentMock;

    /** @var Manager|MockObject */
    protected $messageManagerMock;

    /**
     * @var OrderManagementInterface|MockObject
     */
    protected $orderManagementMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    protected $orderRepositoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(Context::class, [
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
        $this->orderManagementMock = $this->getMockBuilder(OrderManagementInterface::class)
            ->getMockForAbstractClass();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->setMethods(['getPayment'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->createPartialMock(
            Manager::class,
            ['addSuccessMessage', 'addErrorMessage']
        );

        $this->resultRedirectFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );

        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['getIsTransactionApproved'])
            ->onlyMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectMock = $this->createPartialMock(
            Redirect::class,
            ['setPath']
        );

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->reviewPayment = (new ObjectManager($this))->getObject(
            ReviewPayment::class,
            [
                'context' => $this->contextMock,
                'orderManager' => $this->orderManagementMock,
                'orderRepository' => $this->orderRepositoryMock
            ]
        );
    }

    /**
     * testExecuteUpdateAction
     */
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

        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/order/view')
            ->willReturnSelf();

        $result = $this->reviewPayment->execute();
        $this->assertEquals($this->resultRedirectMock, $result);
    }
}
