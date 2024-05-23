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
use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Exception\CouldNotInvoiceException;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\InvoiceOrder;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\NotifierInterface;
use Magento\Sales\Model\Order\InvoiceDocumentFactory;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\PaymentAdapterInterface;
use Magento\Sales\Model\Order\Validation\InvoiceOrderInterface;
use Magento\Sales\Model\OrderMutex;
use Magento\Sales\Model\ValidatorResultInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceOrderTest extends TestCase
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
     * @var InvoiceDocumentFactory|MockObject
     */
    private $invoiceDocumentFactoryMock;

    /**
     * @var InvoiceOrderInterface|MockObject
     */
    private $invoiceOrderValidatorMock;

    /**
     * @var PaymentAdapterInterface|MockObject
     */
    private $paymentAdapterMock;

    /**
     * @var OrderStateResolverInterface|MockObject
     */
    private $orderStateResolverMock;

    /**
     * @var OrderConfig|MockObject
     */
    private $configMock;

    /**
     * @var InvoiceRepository|MockObject
     */
    private $invoiceRepositoryMock;

    /**
     * @var NotifierInterface|MockObject
     */
    private $notifierInterfaceMock;

    /**
     * @var InvoiceOrder|MockObject
     */
    private $invoiceOrder;

    /**
     * @var InvoiceCreationArgumentsInterface|MockObject
     */
    private $invoiceCommentCreationMock;

    /**
     * @var InvoiceCommentCreationInterface|MockObject
     */
    private $invoiceCreationArgumentsMock;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var InvoiceInterface|MockObject
     */
    private $invoiceMock;

    /**
     * @var AdapterInterface
     */
    private $adapterInterface;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ValidatorResultInterface|MockObject
     */
    private $errorMessagesMock;

    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceDocumentFactoryMock = $this->getMockBuilder(InvoiceDocumentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentAdapterMock = $this->getMockBuilder(PaymentAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderStateResolverMock = $this->getMockBuilder(OrderStateResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configMock = $this->getMockBuilder(OrderConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceRepositoryMock = $this->getMockBuilder(InvoiceRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notifierInterfaceMock = $this->getMockBuilder(NotifierInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceCommentCreationMock = $this->getMockBuilder(InvoiceCommentCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceCreationArgumentsMock = $this->getMockBuilder(InvoiceCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceMock = $this->getMockBuilder(InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->adapterInterface = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceOrderValidatorMock = $this->getMockBuilder(InvoiceOrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->errorMessagesMock = $this->getMockBuilder(ValidatorResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasMessages', 'getMessages', 'addMessage'])
            ->getMockForAbstractClass();

        $this->invoiceOrder = new InvoiceOrder(
            $this->resourceConnectionMock,
            $this->orderRepositoryMock,
            $this->invoiceDocumentFactoryMock,
            $this->paymentAdapterMock,
            $this->orderStateResolverMock,
            $this->configMock,
            $this->invoiceRepositoryMock,
            $this->invoiceOrderValidatorMock,
            $this->notifierInterfaceMock,
            $this->loggerMock,
            new OrderMutex($this->resourceConnectionMock)
        );
    }

    /**
     * @param int $orderId
     * @param bool $capture
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @throws CouldNotInvoiceException
     * @throws DocumentValidationException
     * @dataProvider dataProvider
     */
    public function testOrderInvoice($orderId, $capture, $items, $notify, $appendComment)
    {
        $this->mockConnection($orderId);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);
        $this->invoiceDocumentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->orderMock,
                $items,
                $this->invoiceCommentCreationMock,
                ($appendComment && $notify),
                $this->invoiceCreationArgumentsMock
            )->willReturn($this->invoiceMock);
        $this->invoiceOrderValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->orderMock,
                $this->invoiceMock,
                $capture,
                $items,
                $notify,
                $appendComment,
                $this->invoiceCommentCreationMock,
                $this->invoiceCreationArgumentsMock
            )
            ->willReturn($this->errorMessagesMock);
        $hasMessages = false;
        $this->errorMessagesMock->expects($this->once())
            ->method('hasMessages')->willReturn($hasMessages);
        $this->paymentAdapterMock->expects($this->once())
            ->method('pay')
            ->with($this->orderMock, $this->invoiceMock, $capture)
            ->willReturn($this->orderMock);
        $this->orderStateResolverMock->expects($this->once())
            ->method('getStateForOrder')
            ->with($this->orderMock, [OrderStateResolverInterface::IN_PROGRESS])
            ->willReturn(Order::STATE_PROCESSING);
        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);
        $this->configMock->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn('Processing');
        $this->orderMock->expects($this->once())
            ->method('setStatus')
            ->with('Processing')
            ->willReturnSelf();
        $this->invoiceMock->expects($this->once())
            ->method('setState')
            ->with(Invoice::STATE_PAID)
            ->willReturnSelf();
        $this->invoiceRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->invoiceMock)
            ->willReturn($this->invoiceMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturn($this->orderMock);
        if ($notify) {
            $this->notifierInterfaceMock->expects($this->once())
                ->method('notify')
                ->with($this->orderMock, $this->invoiceMock, $this->invoiceCommentCreationMock);
        }
        $this->invoiceMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn(2);

        $this->assertEquals(
            2,
            $this->invoiceOrder->execute(
                $orderId,
                $capture,
                $items,
                $notify,
                $appendComment,
                $this->invoiceCommentCreationMock,
                $this->invoiceCreationArgumentsMock
            )
        );
    }

    public function testDocumentValidationException()
    {
        $this->expectException('Magento\Sales\Api\Exception\DocumentValidationExceptionInterface');
        $orderId = 1;
        $capture = true;
        $items = [1 => 2];
        $notify = true;
        $appendComment = true;
        $errorMessages = ['error1', 'error2'];
        $this->mockConnection($orderId);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->invoiceDocumentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->orderMock,
                $items,
                $this->invoiceCommentCreationMock,
                ($appendComment && $notify),
                $this->invoiceCreationArgumentsMock
            )->willReturn($this->invoiceMock);

        $this->invoiceOrderValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->orderMock,
                $this->invoiceMock,
                $capture,
                $items,
                $notify,
                $appendComment,
                $this->invoiceCommentCreationMock,
                $this->invoiceCreationArgumentsMock
            )
            ->willReturn($this->errorMessagesMock);
        $hasMessages = true;

        $this->errorMessagesMock->expects($this->once())
            ->method('hasMessages')->willReturn($hasMessages);
        $this->errorMessagesMock->expects($this->once())
            ->method('getMessages')->willReturn($errorMessages);

        $this->invoiceOrder->execute(
            $orderId,
            $capture,
            $items,
            $notify,
            $appendComment,
            $this->invoiceCommentCreationMock,
            $this->invoiceCreationArgumentsMock
        );
    }

    public function testCouldNotInvoiceException()
    {
        $this->expectException('Magento\Sales\Api\Exception\CouldNotInvoiceExceptionInterface');
        $orderId = 1;
        $items = [1 => 2];
        $capture = true;
        $notify = true;
        $appendComment = true;
        $this->mockConnection($orderId);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->invoiceDocumentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->orderMock,
                $items,
                $this->invoiceCommentCreationMock,
                ($appendComment && $notify),
                $this->invoiceCreationArgumentsMock
            )->willReturn($this->invoiceMock);

        $this->invoiceOrderValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->orderMock,
                $this->invoiceMock,
                $capture,
                $items,
                $notify,
                $appendComment,
                $this->invoiceCommentCreationMock,
                $this->invoiceCreationArgumentsMock
            )
            ->willReturn($this->errorMessagesMock);

        $hasMessages = false;
        $this->errorMessagesMock->expects($this->once())
            ->method('hasMessages')->willReturn($hasMessages);

        $e = new \Exception();
        $this->paymentAdapterMock->expects($this->once())
            ->method('pay')
            ->with($this->orderMock, $this->invoiceMock, $capture)
            ->willThrowException($e);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($e);

        $this->adapterInterface->expects($this->once())
            ->method('rollBack');

        $this->invoiceOrder->execute(
            $orderId,
            $capture,
            $items,
            $notify,
            $appendComment,
            $this->invoiceCommentCreationMock,
            $this->invoiceCreationArgumentsMock
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'TestWithNotifyTrue' => [1, true, [1 => 2], true, true],
            'TestWithNotifyFalse' => [1, true, [1 => 2], false, true],
        ];
    }

    /**
     * @param int $orderId
     */
    private function mockConnection(int $orderId): void
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
