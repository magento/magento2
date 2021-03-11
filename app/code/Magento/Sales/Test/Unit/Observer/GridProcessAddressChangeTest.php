<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Observer;

/**
 * Class GridProcessAddressChangeTest
 */
class GridProcessAddressChangeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Observer\GridProcessAddressChange
     */
    protected $observer;

    /**
     * @var \Magento\Sales\Model\ResourceModel\GridPool|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $gridPoolMock;

    /**
     * @var \Magento\Framework\Event\ObserverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventObserverMock;

    protected function setUp(): void
    {
        $this->gridPoolMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\GridPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrderId'])
            ->getMock();
        $this->observer = new \Magento\Sales\Observer\GridProcessAddressChange($this->gridPoolMock);
    }

    public function testGridsReindex()
    {
        $this->eventObserverMock->expects($this->once())
            ->method('getOrderId')
            ->willReturn(100500);
        $this->gridPoolMock->expects($this->once())
            ->method('refreshByOrderId')
            ->with(100500);
        $this->assertNull($this->observer->execute($this->eventObserverMock));
    }
}
