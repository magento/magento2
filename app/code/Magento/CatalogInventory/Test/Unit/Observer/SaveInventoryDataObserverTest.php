<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Observer\SaveInventoryDataObserver;

class SaveInventoryDataObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveInventoryDataObserver
     */
    protected $observer;

    /**
     * @var \Magento\CatalogInventory\Api\StockIndexInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockIndex;

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
        $this->stockIndex = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockIndexInterface',
            ['rebuild'],
            '',
            false
        );

        $this->event = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));

        $this->observer = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\CatalogInventory\Observer\SaveInventoryDataObserver',
            [
                'stockIndex' => $this->stockIndex,
            ]
        );
    }

    public function testSaveInventoryData()
    {
        $productId = 4;
        $websiteId = 5;
        $stockData = null;
        $websitesChanged = true;
        $statusChanged = true;

        $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId'], [], '', false);
        $store->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));

        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getStockData', 'getIsChangedWebsites', 'dataHasChangedFor', 'getId', 'getStore'],
            [],
            '',
            false
        );
        $product->expects($this->once())->method('getStockData')->will($this->returnValue($stockData));
        $product->expects($this->any())->method('getIsChangedWebsites')->will($this->returnValue($websitesChanged));
        $product->expects($this->any())->method('dataHasChangedFor')->will($this->returnValue($statusChanged));
        $product->expects($this->once())->method('getId')->will($this->returnValue($productId));
        $product->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $this->stockIndex->expects($this->once())->method('rebuild')->will($this->returnValue(true));

        $this->event->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($product));

        $this->observer->execute($this->eventObserver);
    }
}
