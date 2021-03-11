<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Observer\AddInventoryDataObserver;

class AddInventoryDataObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AddInventoryDataObserver
     */
    protected $observer;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockHelper;

    /**
     * @var \Magento\Framework\Event|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $event;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventObserver;

    protected function setUp(): void
    {
        $this->stockHelper = $this->createMock(\Magento\CatalogInventory\Helper\Stock::class);

        $this->event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->observer = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\CatalogInventory\Observer\AddInventoryDataObserver::class,
            [
                'stockHelper' => $this->stockHelper,
            ]
        );
    }

    public function testAddInventoryData()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->event->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $this->stockHelper->expects($this->once())
            ->method('assignStatusToProduct')
            ->with($product)
            ->willReturnSelf();

        $this->observer->execute($this->eventObserver);
    }
}
