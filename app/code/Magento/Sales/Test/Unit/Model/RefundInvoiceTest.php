<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Exception\CouldNotRefundException;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\NotifierInterface;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\RefundAdapterInterface;
use Magento\Sales\Model\Order\Validation\RefundInvoiceInterface;
use Magento\Sales\Model\OrderMutex;
use Magento\Sales\Model\RefundInvoice;
use Magento\Sales\Model\ValidatorResultInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RefundInvoiceTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var InvoiceRepositoryInterface|MockObject
     */
    private $invoiceRepositoryMock;

    /**
     * @var CreditmemoDocumentFactory|MockObject
     */
    private $creditmemoDocumentFactoryMock;

    /**
     * @var RefundAdapterInterface|MockObject
     */
    private $refundAdapterMock;

    /**
     * @var OrderStateResolverInterface|MockObject
     */
    private $orderStateResolverMock;

    /**
     * @var OrderConfig|MockObject
     */
    private $configMock;

    /**
     * @var Order\CreditmemoRepository|MockObject
     */
    private $creditmemoRepositoryMock;

    /**
     * @var NotifierInterface|MockObject
     */
    private $notifierMock;

    /**
     * @var RefundInvoice|MockObject
     */
    private $refundInvoice;

    /**
     * @var CreditmemoCreationArgumentsInterface|MockObject
     */
    private $creditmemoCommentCreationMock;

    /**
     * @var CreditmemoCommentCreationInterface|MockObject
     */
    private $creditmemoCreationArgumentsMock;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var OrderInterface|MockObject
     */
    private $invoiceMock;

    /**
     * @var CreditmemoInterface|MockObject
     */
    private $creditmemoMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapterInterface;

    /**
     * @var CreditmemoItemCreationInterface|MockObject
     */
    private $creditmemoItemCreationMock;

    /**
     * @var RefundInvoiceInterface|MockObject
     */
    private $refundInvoiceValidatorMock;

    /**
     * @var ValidatorResultInterface|MockObject
     */
    private $validationMessagesMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->invoiceRepositoryMock = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditmemoDocumentFactoryMock = $this->getMockBuilder(CreditmemoDocumentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->refundAdapterMock = $this->getMockBuilder(RefundAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->refundInvoiceValidatorMock = $this->getMockBuilder(RefundInvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
            ->onlyMethods(['hasMessages', 'getMessages', 'addMessage'])
            ->getMockForAbstractClass();

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
            $this->loggerMock,
            new OrderMutex($this->resourceConnectionMock)
        );
    }

    /**
     * @param int $invoiceId
     * @param bool $isOnline
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @throws CouldNotRefundException
     * @throws DocumentValidationException
     * @dataProvider dataProvider
     */
    public function testOrderCreditmemo($invoiceId, $isOnline, $items, $notify, $appendComment)
    {
        $this->mockConnection($invoiceId);
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getOrderId')
            ->willReturn($invoiceId);
        $this->orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($invoiceId);
        $this->invoiceRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->willReturn($this->invoiceMock);
        $this->orderRepositoryMock->expects($this->exactly(2))
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
            ->with(Creditmemo::STATE_REFUNDED)
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

    public function testDocumentValidationException()
    {
        $this->expectException('Magento\Sales\Api\Exception\DocumentValidationExceptionInterface');
        $invoiceId = 1;
        $items = [1 => $this->creditmemoItemCreationMock];
        $notify = true;
        $appendComment = true;
        $isOnline = false;
        $errorMessages = ['error1', 'error2'];
        $this->mockConnection($invoiceId);
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getOrderId')
            ->willReturn($invoiceId);
        $this->orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($invoiceId);
        $this->invoiceRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->willReturn($this->invoiceMock);
        $this->orderRepositoryMock->expects($this->exactly(2))
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

    public function testCouldNotCreditmemoException()
    {
        $this->expectException('Magento\Sales\Api\Exception\CouldNotRefundExceptionInterface');
        $invoiceId = 1;
        $items = [1 => $this->creditmemoItemCreationMock];
        $notify = true;
        $appendComment = true;
        $isOnline = false;

        $this->mockConnection($invoiceId);
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getOrderId')
            ->willReturn($invoiceId);
        $this->orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($invoiceId);
        $this->invoiceRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->willReturn($this->invoiceMock);
        $this->orderRepositoryMock->expects($this->exactly(2))
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

    protected function getMockForCreditMemoItem() {
        $creditmemoItemCreationMock = $this->getMockBuilder(CreditmemoItemCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        return $creditmemoItemCreationMock;
    }

    /**
     * @return array
     */
    public static function dataProvider()
    {
        $creditmemoItemCreationMock = static fn (self $testCase) => $testCase->getMockForCreditMemoItem();

        return [
            'TestWithNotifyTrue' => [1, true,  [1 => $creditmemoItemCreationMock], true, true],
            'TestWithNotifyFalse' => [1, true,  [1 => $creditmemoItemCreationMock], false, true],
        ];
    }

    private function mockConnection(int $orderId)
    {
        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('from')
            ->with('sales_order', 'entity_id')
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('entity_id = ?', $orderId)
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('forUpdate')
            ->with(true)
            ->willReturnSelf();
        $this->adapterInterface->expects($this->once())
            ->method('select')
            ->willReturn($select);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterInterface);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->willReturnArgument(0);
    }
}
