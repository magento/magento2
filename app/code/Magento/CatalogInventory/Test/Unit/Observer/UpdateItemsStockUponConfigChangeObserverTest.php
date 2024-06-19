<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\CatalogInventory\Observer\UpdateItemsStockUponConfigChangeObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getWebsite', 'getChangedPaths'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->observer = (new ObjectManager($this))->getObject(
            UpdateItemsStockUponConfigChangeObserver::class,
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
            ->willReturn([Configuration::XML_PATH_MANAGE_STOCK]);

        $this->observer->execute($this->eventObserver);
    }
}
