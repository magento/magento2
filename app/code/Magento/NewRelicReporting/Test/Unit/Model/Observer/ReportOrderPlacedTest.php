<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\NewRelicReporting\Model\Observer\ReportOrderPlaced;

/**
 * Class ReportOrderPlacedTest
 */
class ReportOrderPlacedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportOrderPlaced
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\OrdersFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ordersFactory;

    /**
     * @var \Magento\NewRelicReporting\Model\Orders|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ordersModel;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\NewRelicReporting\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->ordersFactory = $this->getMockBuilder('Magento\NewRelicReporting\Model\OrdersFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->ordersModel = $this->getMockBuilder('Magento\NewRelicReporting\Model\Orders')
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
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
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

        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $event = $this->getMockBuilder('Magento\Framework\Event')
            ->setMethods(['getOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserver->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $order = $this->getMockBuilder('Magento\Sales\Model\Order')->disableOriginalConstructor()->getMock();
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
