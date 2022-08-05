<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Observer\ReportOrderPlaced;
use Magento\NewRelicReporting\Model\Orders;
use Magento\NewRelicReporting\Model\OrdersFactory;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportOrderPlacedTest extends TestCase
{
    /**
     * @var ReportOrderPlaced
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var OrdersFactory|MockObject
     */
    protected $ordersFactory;

    /**
     * @var Orders|MockObject
     */
    protected $ordersModel;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->ordersFactory = $this->getMockBuilder(OrdersFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->ordersModel = $this->getMockBuilder(Orders::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ordersFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->ordersModel);

        $this->model = new ReportOrderPlaced(
            $this->config,
            $this->ordersFactory
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportOrderPlacedModuleDisabledFromConfig()
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(false);

        $this->model->execute($eventObserver);
    }

    /**
     * Test case when module is enabled in config
     *
     * @return void
     */
    public function testReportOrderPlaced()
    {
        $testCustomerId = 1;
        $testTotal = '1.00';
        $testBaseTotal = '1.00';
        $testItemCount = null;
        $testTotalQtyOrderedCount = 1;

        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $event = $this->getMockBuilder(Event::class)
            ->setMethods(['getOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserver->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);
        $order->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($testCustomerId);
        $order->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($testTotal);
        $order->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($testBaseTotal);
        $order->expects($this->once())
            ->method('getTotalItemCount')
            ->willReturn($testItemCount);
        $order->expects($this->once())
            ->method('getTotalQtyOrdered')
            ->willReturn($testTotalQtyOrderedCount);
        $this->ordersModel->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'customer_id' => $testCustomerId,
                    'total' => $testTotal,
                    'total_base' => $testBaseTotal,
                    'item_count' => $testTotalQtyOrderedCount,
                ]
            )
            ->willReturnSelf();
        $this->ordersModel->expects($this->once())
            ->method('save');

        $this->model->execute($eventObserver);
    }
}
