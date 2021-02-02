<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Observer\UpdateItemsStockUponConfigChangeObserver;

class UpdateItemsStockUponConfigChangeObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UpdateItemsStockUponConfigChangeObserver
     */
    protected $observer;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceStockItem;

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
        $this->resourceStockItem = $this->createMock(\Magento\CatalogInventory\Model\ResourceModel\Stock\Item::class);

        $this->event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsite', 'getChangedPaths'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->observer = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\CatalogInventory\Observer\UpdateItemsStockUponConfigChangeObserver::class,
            [
                'resourceStockItem' => $this->resourceStockItem,
            ]
        );
    }

    public function testUpdateItemsStockUponConfigChange()
    {
        $websiteId = 1;
        $this->resourceStockItem->expects($this->once())->method('updateSetOutOfStock');
        $this->resourceStockItem->expects($this->once())->method('updateSetInStock');
        $this->resourceStockItem->expects($this->once())->method('updateLowStockDate');

        $this->event->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteId);
        $this->event->expects($this->once())
            ->method('getChangedPaths')
            ->willReturn([\Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK]);

        $this->observer->execute($this->eventObserver);
    }
}
