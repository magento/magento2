<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\CatalogInventory\Observer\UpdateItemsStockUponConfigChangeObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class UpdateItemsStockUponConfigChangeObserverTest extends TestCase
{
    /**
     * @var UpdateItemsStockUponConfigChangeObserver
     */
    protected $observer;

    /**
     * @var Item|MockObject
     */
    protected $resourceStockItem;

    /**
     * @var Event|MockObject
     */
    protected $event;

    /**
     * @var Observer|MockObject
     */
    protected $eventObserver;

    protected function setUp(): void
    {
        $this->resourceStockItem = $this->createMock(Item::class);

        $this->event = new Event();

        $this->eventObserver = $this->createMock(Observer::class);

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->observer = new UpdateItemsStockUponConfigChangeObserver(
            $this->resourceStockItem
        );
    }

    public function testUpdateItemsStockUponConfigChange()
    {
        $websiteId = 1;
        $this->resourceStockItem->expects($this->once())->method('updateSetOutOfStock');
        $this->resourceStockItem->expects($this->once())->method('updateSetInStock');
        $this->resourceStockItem->expects($this->once())->method('updateLowStockDate');

        $this->event->setWebsite($websiteId);
        $this->event->setChangedPaths([Configuration::XML_PATH_MANAGE_STOCK]);

        $this->observer->execute($this->eventObserver);
    }
}
