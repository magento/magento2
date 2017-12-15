<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Creditmemo\NotifierInterface;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\RefundAdapterInterface;
use Magento\Sales\Model\Order\Validation\RefundInvoiceInterface;
use Magento\Sales\Model\RefundInvoice;
use Magento\Sales\Model\ValidatorResultInterface;
use Psr\Log\LoggerInterface;

/**
 * Class RefundInvoiceTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RefundInvoiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var InvoiceRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceRepositoryMock;

    /**
     * @var CreditmemoDocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoDocumentFactoryMock;

    /**
     * @var RefundAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $refundAdapterMock;

    /**
     * @var OrderStateResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderStateResolverMock;

    /**
     * @var OrderConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Order\CreditmemoRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoRepositoryMock;

    /**
     * @var NotifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notifierMock;

    /**
     * @var RefundInvoice|\PHPUnit_Framework_MockObject_MockObject
     */
    private $refundInvoice;

    /**
     * @var CreditmemoCreationArgumentsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoCommentCreationMock;

    /**
     * @var CreditmemoCommentCreationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoCreationArgumentsMock;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceMock;

    /**
     * @var CreditmemoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterInterface;

    /**
     * @var CreditmemoItemCreationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoItemCreationMock;

    /**
     * @var RefundInvoiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $refundInvoiceValidatorMock;

    /**
     * @var ValidatorResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationMessagesMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceRepositoryMock = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoDocumentFactoryMock = $this->getMockBuilder(CreditmemoDocumentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->refundAdapterMock = $this->getMockBuilder(RefundAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->refundInvoiceValidatorMock = $this->getMockBuilder(RefundInvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderStateResolverMock = $this->getMockBuilder(OrderStateResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(OrderConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditmemoRepositoryMock = $this->getMockBuilder(CreditmemoRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->notifierMock = $this->getMockBuilder(NotifierInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoCommentCreationMock = $this->getMockBuilder(CreditmemoCommentCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoCreationArgumentsMock = $this->getMockBuilder(CreditmemoCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceMock = $this->getMockBuilder(InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->adapterInterface = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoItemCreationMock = $this->getMockBuilder(CreditmemoItemCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validationMessagesMock = $this->getMockBuilder(ValidatorResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasMessages', 'getMessages', 'addMessage'])
            ->getMock();

        $this->refundInvoice = new RefundInvoice(
            $this->resourceConnectionMock,
            $this->orderStateResolverMock,
            $this->orderRepositoryMock,
            $this->invoiceRepositoryMock,
            $this->refundInvoiceValidatorMock,
            $this->creditmemoRepositoryMock,
            $this->refundAdapterMock,
            $this->creditmemoDocumentFactoryMock,
            $this->notifierMock,
            $this->configMock,
            $this->loggerMock
        );
    }

    /**
     * @param int $invoiceId
     * @param bool $isOnline
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @throws \Magento\Sales\Exception\CouldNotRefundException
     * @throws \Magento\Sales\Exception\DocumentValidationException
     * @dataProvider dataProvider
     */
    public function testOrderCreditmemo($invoiceId, $isOnline, $items, $notify, $appendComment)
    {
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterInterface);
        $this->invoiceRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->invoiceMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);
        $this->creditmemoDocumentFactoryMock->expects($this->once())
            ->method('createFromInvoice')
            ->with(
                $this->invoiceMock,
                $items,
                $this->creditmemoCommentCreationMock,
                ($appendComment && $notify),
                $this->creditmemoCreationArgumentsMock
            )->willReturn($this->creditmemoMock);
        $this->refundInvoiceValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->invoiceMock,
                $this->orderMock,
                $this->creditmemoMock,
                $items,
                $isOnline,
                $notify,
                $appendComment,
                $this->creditmemoCommentCreationMock,
                $this->creditmemoCreationArgumentsMock
            )
            ->willReturn($this->validationMessagesMock);
        $hasMessages = false;
        $this->validationMessagesMock->expects($this->once())
            ->method('hasMessages')->willReturn($hasMessages);
        $this->refundAdapterMock->expects($this->once())
            ->method('refund')
            ->with($this->creditmemoMock, $this->orderMock)
            ->willReturn($this->orderMock);
        $this->orderStateResolverMock->expects($this->once())
            ->method('getStateForOrder')
            ->with($this->orderMock, [])
            ->willReturn(Order::STATE_CLOSED);
        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_CLOSED)
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_CLOSED);
        $this->configMock->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_CLOSED)
            ->willReturn('Closed');
        $this->orderMock->expects($this->once())
            ->method('setStatus')
            ->with('Closed')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setState')
            ->with(\Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED)
            ->willReturnSelf();
        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->creditmemoMock)
            ->willReturn($this->creditmemoMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturn($this->orderMock);
        if ($notify) {
            $this->notifierMock->expects($this->once())
                ->method('notify')
                ->with($this->orderMock, $this->creditmemoMock, $this->creditmemoCommentCreationMock);
        }
        $this->creditmemoMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn(2);

        $this->assertEquals(
            2,
            $this->refundInvoice->execute(
                $invoiceId,
                $items,
                true,
                $notify,
                $appendComment,
                $this->creditmemoCommentCreationMock,
                $this->creditmemoCreationArgumentsMock
            )
        );
    }

    /**
     * @expectedException \Magento\Sales\Api\Exception\DocumentValidationExceptionInterface
     */
    public function testDocumentValidationException()
    {
        $invoiceId = 1;
        $items = [1 => $this->creditmemoItemCreationMock];
        $notify = true;
        $appendComment = true;
        $isOnline = false;
        $errorMessages = ['error1', 'error2'];

        $this->invoiceRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->invoiceMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->creditmemoDocumentFactoryMock->expects($this->once())
            ->method('createFromInvoice')
            ->with(
                $this->invoiceMock,
                $items,
                $this->creditmemoCommentCreationMock,
                ($appendComment && $notify),
                $this->creditmemoCreationArgumentsMock
            )->willReturn($this->creditmemoMock);

        $this->refundInvoiceValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->invoiceMock,
                $this->orderMock,
                $this->creditmemoMock,
                $items,
                $isOnline,
                $notify,
                $appendComment,
                $this->creditmemoCommentCreationMock,
                $this->creditmemoCreationArgumentsMock
            )
            ->willReturn($this->validationMessagesMock);
        $hasMessages = true;
        $this->validationMessagesMock->expects($this->once())
            ->method('hasMessages')->willReturn($hasMessages);
        $this->validationMessagesMock->expects($this->once())
            ->method('getMessages')->willReturn($errorMessages);

        $this->assertEquals(
            $errorMessages,
            $this->refundInvoice->execute(
                $invoiceId,
                $items,
                false,
                $notify,
                $appendComment,
                $this->creditmemoCommentCreationMock,
                $this->creditmemoCreationArgumentsMock
            )
        );
    }

    /**
     * @expectedException \Magento\Sales\Api\Exception\CouldNotRefundExceptionInterface
     */
    public function testCouldNotCreditmemoException()
    {
        $invoiceId = 1;
        $items = [1 => $this->creditmemoItemCreationMock];
        $notify = true;
        $appendComment = true;
        $isOnline = false;
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterInterface);

        $this->invoiceRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->invoiceMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->creditmemoDocumentFactoryMock->expects($this->once())
            ->method('createFromInvoice')
            ->with(
                $this->invoiceMock,
                $items,
                $this->creditmemoCommentCreationMock,
                ($appendComment && $notify),
                $this->creditmemoCreationArgumentsMock
            )->willReturn($this->creditmemoMock);

        $this->refundInvoiceValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->invoiceMock,
                $this->orderMock,
                $this->creditmemoMock,
                $items,
                $isOnline,
                $notify,
                $appendComment,
                $this->creditmemoCommentCreationMock,
                $this->creditmemoCreationArgumentsMock
            )
            ->willReturn($this->validationMessagesMock);
        $hasMessages = false;
        $this->validationMessagesMock->expects($this->once())
            ->method('hasMessages')->willReturn($hasMessages);
        $e = new \Exception();

        $this->refundAdapterMock->expects($this->once())
            ->method('refund')
            ->with($this->creditmemoMock, $this->orderMock)
            ->willThrowException($e);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($e);

        $this->adapterInterface->expects($this->once())
            ->method('rollBack');

        $this->refundInvoice->execute(
            $invoiceId,
            $items,
            false,
            $notify,
            $appendComment,
            $this->creditmemoCommentCreationMock,
            $this->creditmemoCreationArgumentsMock
        );
    }

    public function dataProvider()
    {
        $creditmemoItemCreationMock = $this->getMockBuilder(CreditmemoItemCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return [
            'TestWithNotifyTrue' => [1, true,  [1 => $creditmemoItemCreationMock], true, true],
            'TestWithNotifyFalse' => [1, true,  [1 => $creditmemoItemCreationMock], false, true],
        ];
    }
}
