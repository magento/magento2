<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Helper\Stock;
use Magento\CatalogInventory\Observer\AddInventoryDataObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProduct'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->observer = (new ObjectManager($this))->getObject(
            AddInventoryDataObserver::class,
            [
                'stockHelper' => $this->stockHelper,
            ]
        );
    }

    public function testAddInventoryData()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->event->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $this->stockHelper->expects($this->once())
            ->method('assignStatusToProduct')
            ->with($product)->willReturnSelf();

        $this->observer->execute($this->eventObserver);
    }
}
