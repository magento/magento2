<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\NewRelicReporting\Model\Observer\ReportOrderPlacedToNewRelic;

/**
 * Class ReportOrderPlacedToNewRelicTest
 */
class ReportOrderPlacedToNewRelicTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportOrderPlacedToNewRelic
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\NewRelicWrapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $newRelicWrapper;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(\Magento\NewRelicReporting\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->newRelicWrapper = $this->getMockBuilder(\Magento\NewRelicReporting\Model\NewRelicWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['addCustomParameter'])
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
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
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

        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->setMethods(['getOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserver->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
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
