<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Observer\UpdateItemsStockUponConfigChangeObserver;

class UpdateItemsStockUponConfigChangeObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateItemsStockUponConfigChangeObserver
     */
    protected $observer;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceStock;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserver;

    protected function setUp()
    {
        $this->resourceStock = $this->getMock(
            \Magento\CatalogInventory\Model\ResourceModel\Stock::class,
            [],
            [],
            '',
            false
        );

        $this->event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsite'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));

        $this->observer = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\CatalogInventory\Observer\UpdateItemsStockUponConfigChangeObserver::class,
            [
                'resourceStock' => $this->resourceStock,
            ]
        );
    }

    public function testUpdateItemsStockUponConfigChange()
    {
        $websiteId = 1;
        $this->resourceStock->expects($this->once())->method('updateSetOutOfStock');
        $this->resourceStock->expects($this->once())->method('updateSetInStock');
        $this->resourceStock->expects($this->once())->method('updateLowStockDate');

        $this->event->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($websiteId));

        $this->observer->execute($this->eventObserver);
    }
}
