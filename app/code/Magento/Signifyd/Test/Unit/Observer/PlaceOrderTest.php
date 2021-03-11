<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Signifyd\Api\CaseCreationServiceInterface;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Observer\PlaceOrder;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use Psr\Log\LoggerInterface;

class PlaceOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var CaseCreationServiceInterface|MockObject
     */
    private $creationService;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var Observer|MockObject
     */
    private $observer;

    /**
     * @var Event|MockObject
     */
    private $event;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderEntity;

    /**
     * @var PlaceOrder
     */
    private $placeOrder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isActive'])
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creationService = $this->getMockBuilder(CaseCreationServiceInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['createForOrder'])
            ->getMockForAbstractClass();

        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $this->placeOrder = new PlaceOrder(
            $this->config,
            $this->creationService,
            $this->logger
        );
    }

    /**
     * Checks a test case when Signifyd module is disabled.
     *
     * @covers \Magento\Signifyd\Observer\PlaceOrder::execute
     */
    public function testExecuteWithDisabledModule()
    {
        $orderId = 1;
        $storeId = 2;
        $this->withActiveSignifydIntegration(false, $storeId);
        $this->withOrderEntity($orderId, $storeId);

        $this->creationService->expects(self::never())
            ->method('createForOrder');

        $this->placeOrder->execute($this->observer);
    }

    /**
     * Checks a test case when the observer event returns empty an order entity.
     *
     * @covers \Magento\Signifyd\Observer\PlaceOrder::execute
     */
    public function testExecuteWithoutOrder()
    {
        $this->withActiveSignifydIntegration(true);
        $this->withOrderEntity(null, null);

        $this->creationService->expects(self::never())
            ->method('createForOrder');

        $this->placeOrder->execute($this->observer);
    }

    /**
     * Checks a test case when the order placed with offline payment method.
     *
     * @covers \Magento\Signifyd\Observer\PlaceOrder::execute
     */
    public function testExecuteWithOfflinePayment()
    {
        $orderId = 1;
        $storeId = 2;
        $this->withActiveSignifydIntegration(true, $storeId);
        $this->withOrderEntity($orderId, $storeId);
        $this->withAvailablePaymentMethod(false);

        $this->creationService->expects(self::never())
            ->method('createForOrder');

        $this->placeOrder->execute($this->observer);
    }

    /**
     * Checks a test case when case creation service fails.
     *
     * @covers \Magento\Signifyd\Observer\PlaceOrder::execute
     */
    public function testExecuteWithFailedCaseCreation()
    {
        $orderId = 1;
        $storeId = 2;
        $exceptionMessage = __('Case with the same order id already exists.');

        $this->withActiveSignifydIntegration(true, $storeId);
        $this->withOrderEntity($orderId, $storeId);
        $this->withAvailablePaymentMethod(true);

        $this->creationService->method('createForOrder')
            ->with(self::equalTo($orderId))
            ->willThrowException(new AlreadyExistsException($exceptionMessage));

        $this->logger->method('error')
            ->with(self::equalTo($exceptionMessage));

        $result = $this->placeOrder->execute($this->observer);
        $this->assertNull($result);
    }

    /**
     * Checks a test case when observer successfully calls case creation service.
     *
     * @covers \Magento\Signifyd\Observer\PlaceOrder::execute
     */
    public function testExecute()
    {
        $orderId = 1;
        $storeId = 2;

        $this->withActiveSignifydIntegration(true, $storeId);
        $this->withOrderEntity($orderId, $storeId);
        $this->withAvailablePaymentMethod(true);

        $this->creationService
            ->method('createForOrder')
            ->with(self::equalTo($orderId));

        $this->logger->expects(self::never())
            ->method('error');

        $this->placeOrder->execute($this->observer);
    }

    public function testExecuteWithOrderPendingPayment()
    {
        $orderId = 1;
        $storeId = 2;

        $this->withActiveSignifydIntegration(true, $storeId);
        $this->withOrderEntity($orderId, $storeId);
        $this->orderEntity->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);
        $this->withAvailablePaymentMethod(true);

        $this->creationService->expects(self::never())
            ->method('createForOrder');

        $this->placeOrder->execute($this->observer);
    }

    /**
     * Specifies order entity mock execution.
     *
     * @param int|null $orderId
     * @param int|null $storeId
     * @return void
     */
    private function withOrderEntity($orderId, $storeId): void
    {
        $this->orderEntity = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderEntity->method('getEntityId')
            ->willReturn($orderId);
        $this->orderEntity->method('getStoreId')
            ->willReturn($storeId);

        $this->observer->method('getEvent')
            ->willReturn($this->event);

        $this->event->method('getData')
            ->with('order')
            ->willReturn($this->orderEntity);
    }

    /**
     * Specifies config mock execution.
     *
     * @param bool $isActive
     * @param int|null $storeId
     * @return void
     */
    private function withActiveSignifydIntegration(bool $isActive, $storeId = null): void
    {
        $this->config->method('isActive')
            ->with($storeId)
            ->willReturn($isActive);
    }

    /**
     * Specifies payment method mock execution.
     *
     * @param bool $isAvailable
     * @return void
     */
    private function withAvailablePaymentMethod($isAvailable)
    {
        /** @var MethodInterface|MockObject $paymentMethod */
        $paymentMethod = $this->getMockBuilder(MethodInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /**
         * The code depends on implementation but not interface
         * because order payment implements two interfaces
         */
        /** @var Payment|MockObject $orderPayment */
        $orderPayment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderEntity->method('getPayment')
            ->willReturn($orderPayment);

        $orderPayment->method('getMethodInstance')
            ->willReturn($paymentMethod);

        $paymentMethod->method('isOffline')
            ->willReturn(!$isAvailable);
    }
}
