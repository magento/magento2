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
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Model\Observer\ReportOrderPlacedToNewRelic;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportOrderPlacedToNewRelicTest extends TestCase
{
    /**
     * @var ReportOrderPlacedToNewRelic
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var NewRelicWrapper|MockObject
     */
    protected $newRelicWrapper;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->newRelicWrapper = $this->getMockBuilder(NewRelicWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addCustomParameter'])
            ->getMock();

        $this->model = new ReportOrderPlacedToNewRelic(
            $this->config,
            $this->newRelicWrapper
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportOrderPlacedToNewRelicModuleDisabledFromConfig()
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
    public function testReportOrderPlacedToNewRelic()
    {
        $testTotal = '1.00';
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
            ->addMethods(['getOrder'])
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
            ->method('getBaseGrandTotal')
            ->willReturn($testTotal);
        $order->expects($this->once())
            ->method('getTotalItemCount')
            ->willReturn($testItemCount);
        $order->expects($this->once())
            ->method('getTotalQtyOrdered')
            ->willReturn($testTotalQtyOrderedCount);
        $this->newRelicWrapper->expects($this->exactly(3))
            ->method('addCustomParameter')
            ->willReturn(true);

        $this->model->execute($eventObserver);
    }
}
