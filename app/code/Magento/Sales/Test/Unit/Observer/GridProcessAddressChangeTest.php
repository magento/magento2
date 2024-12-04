<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\ResourceModel\GridPool;
use Magento\Sales\Observer\GridProcessAddressChange;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GridProcessAddressChangeTest extends TestCase
{
    /**
     * @var GridProcessAddressChange
     */
    protected $observer;

    /**
     * @var GridPool|MockObject
     */
    protected $gridPoolMock;

    /**
     * @var ObserverInterface|MockObject
     */
    protected $eventObserverMock;

    protected function setUp(): void
    {
        $this->gridPoolMock = $this->getMockBuilder(GridPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOrderId'])
            ->getMock();
        $this->observer = new GridProcessAddressChange($this->gridPoolMock);
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
