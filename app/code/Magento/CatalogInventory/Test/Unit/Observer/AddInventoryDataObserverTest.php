<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Helper\Stock;
use Magento\CatalogInventory\Observer\AddInventoryDataObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class AddInventoryDataObserverTest extends TestCase
{
    /**
     * @var AddInventoryDataObserver
     */
    protected $observer;

    /**
     * @var Stock|MockObject
     */
    protected $stockHelper;

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
        $this->stockHelper = $this->createMock(Stock::class);

        $this->event = new Event();

        $this->eventObserver = $this->createMock(Observer::class);
        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->observer = new AddInventoryDataObserver(
            $this->stockHelper
        );
    }

    public function testAddInventoryData()
    {
        $product = $this->createMock(Product::class);

        $this->event->setProduct($product);

        $this->stockHelper->expects($this->once())
            ->method('assignStatusToProduct')
            ->with($product)->willReturnSelf();

        $this->observer->execute($this->eventObserver);
    }
}
