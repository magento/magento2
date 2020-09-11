<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\Data\OrderInterface;
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
use Magento\Sales\Model\Order\Validation\RefundOrderInterface;
use Magento\Sales\Model\RefundOrder;
use Magento\Sales\Model\ValidatorResultInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RefundOrderTest extends TestCase
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
     * @var RefundOrder|MockObject
     */
    private $refundOrder;

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
     * @var CreditmemoInterface|MockObject
     */
    private $creditmemoMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapterInterface;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var RefundOrderInterface|MockObject
     */
    private $refundOrderValidatorMock;

    /**
     * @var CreditmemoItemCreationInterface|MockObject
     */
    private $creditmemoItemCreationMock;

    /**
     * @var ValidatorResultInterface|MockObject
     */
    private $validationMessagesMock;

    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditmemoDocumentFactoryMock = $this->getMockBuilder(CreditmemoDocumentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->refundOrderValidatorMock = $this->getMockBuilder(RefundOrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->refundAdapterMock = $this->getMockBuilder(RefundAdapterInterface::class)
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
            ->getMockForAbstractClass();

        $this->refundOrder = new RefundOrder(
            $this->resourceConnectionMock,
            $this->orderStateResolverMock,
            $this->orderRepositoryMock,
            $this->creditmemoRepositoryMock,
            $this->refundAdapterMock,
            $this->creditmemoDocumentFactoryMock,
            $this->refundOrderValidatorMock,
            $this->notifierMock,
            $this->configMock,
            $this->loggerMock
        );
    }

    /**
     * @param int $orderId
     * @param bool $notify
     * @param bool $appendComment
     * @throws CouldNotRefundException
     * @throws DocumentValidationException
     * @dataProvider dataProvider
     */
    public function testOrderCreditmemo($orderId, $notify, $appendComment)
    {
        $items = [$this->creditmemoItemCreationMock];
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterInterface);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);
        $this->creditmemoDocumentFactoryMock->expects($this->once())
            ->method('createFromOrder')
            ->with(
                $this->orderMock,
                $items,
                $this->creditmemoCommentCreationMock,
                ($appendComment && $notify),
                $this->creditmemoCreationArgumentsMock
            )->willReturn($this->creditmemoMock);
        $this->refundOrderValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->orderMock,
                $this->creditmemoMock,
                $items,
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
        $this->configMock->expects($this->once())
            ->method('getStateStatuses')
            ->willReturn(['first, second']);
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
            $this->refundOrder->execute(
                $orderId,
                $items,
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
        $orderId = 1;
        $items = [$this->creditmemoItemCreationMock];
        $notify = true;
        $appendComment = true;
        $errorMessages = ['error1', 'error2'];

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->creditmemoDocumentFactoryMock->expects($this->once())
            ->method('createFromOrder')
            ->with(
                $this->orderMock,
                $items,
                $this->creditmemoCommentCreationMock,
                ($appendComment && $notify),
                $this->creditmemoCreationArgumentsMock
            )->willReturn($this->creditmemoMock);

        $this->refundOrderValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->orderMock,
                $this->creditmemoMock,
                $items,
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
            $this->refundOrder->execute(
                $orderId,
                $items,
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
        $orderId = 1;
        $items = [$this->creditmemoItemCreationMock];
        $notify = true;
        $appendComment = true;
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterInterface);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);
        $this->creditmemoDocumentFactoryMock->expects($this->once())
            ->method('createFromOrder')
            ->with(
                $this->orderMock,
                $items,
                $this->creditmemoCommentCreationMock,
                ($appendComment && $notify),
                $this->creditmemoCreationArgumentsMock
            )->willReturn($this->creditmemoMock);
        $this->refundOrderValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->orderMock,
                $this->creditmemoMock,
                $items,
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

        $this->refundOrder->execute(
            $orderId,
            $items,
            $notify,
            $appendComment,
            $this->creditmemoCommentCreationMock,
            $this->creditmemoCreationArgumentsMock
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'TestWithNotifyTrue' => [1, true, true],
            'TestWithNotifyFalse' => [1, false, true],
        ];
    }
}
