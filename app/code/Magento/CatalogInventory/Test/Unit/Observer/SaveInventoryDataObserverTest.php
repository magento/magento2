<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockConfiguration;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\stockResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockResolver;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemRepository;

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
        $this->stockIndex = $this->getMockBuilder('Magento\CatalogInventory\Api\StockIndexInterface')
            ->disableOriginalConstructor()
            ->setMethods(['rebuild'])
            ->getMockForAbstractClass();

        $this->stockConfiguration = $this->getMockBuilder('Magento\CatalogInventory\Api\StockConfigurationInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultScopeId'])
            ->getMockForAbstractClass();

        $this->stockResolver = $this->getMockBuilder('Magento\CatalogInventory\Model\Spi\StockResolverInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getStockId'])
            ->getMockForAbstractClass();

        $this->stockRegistry = $this->getMockBuilder('Magento\CatalogInventory\Api\StockRegistryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem'])
            ->getMockForAbstractClass();

        $this->stockItemRepository = $this->getMockBuilder('Magento\CatalogInventory\Api\StockItemRepositoryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMockForAbstractClass();

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
                'stockConfiguration' => $this->stockConfiguration,
                'stockResolver' => $this->stockResolver,
                'stockRegistry' => $this->stockRegistry,
                'stockItemRepository' => $this->stockItemRepository
            ]
        );
    }

    public function testSaveInventoryDataWithoutStockData()
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

    public function testSaveInventoryDataWithStockData()
    {
        $stockItemData = [
            'qty' => 4,
            'product_id' => 2,
            'website_id' => 3,
            'stock_id' => 1,
            'qty_correction' => -1
        ];

        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getStockData', 'getId', 'getData'],
            [],
            '',
            false
        );
        $product->expects($this->exactly(2))->method('getStockData')->will($this->returnValue(
            ['qty' => $stockItemData['qty']]
        ));
        $product->expects($this->once())->method('getId')->will($this->returnValue($stockItemData['product_id']));
        $product->expects($this->any())->method('getData')->willReturnMap(
            [
                ['stock_data/original_inventory_qty', null, $stockItemData['qty']+1]
            ]
        );
        $this->stockConfiguration->expects($this->once())->method('getDefaultScopeId')
            ->willReturn($stockItemData['website_id']);
        $this->stockResolver->expects($this->once())->method('getStockId')
            ->with($stockItemData['product_id'], $stockItemData['website_id'])
            ->willReturn($stockItemData['stock_id']);
        $stockItem = $this->getMockBuilder('\Magento\CatalogInventory\Api\Data\StockItemInterface')
            ->disableOriginalConstructor()
            ->setMethods(['addData'])
            ->getMockForAbstractClass();
        $this->stockRegistry->expects($this->once())->method('getStockItem')
            ->with($stockItemData['product_id'], $stockItemData['website_id'])
            ->willReturn($stockItem);
        $stockItem->expects($this->once())->method('addData')->with($stockItemData)->willReturnSelf();
        $this->stockItemRepository->expects($this->once())->method('save')->with($stockItem);
        $this->event->expects($this->once())->method('getProduct')->will($this->returnValue($product));

        $this->observer->execute($this->eventObserver);
    }
}
