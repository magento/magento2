<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class StockRegistryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockRegistryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryProvider;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stock;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItem;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockStatusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatus;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    protected $productId = 111;
    protected $productSku = 'simple';
    protected $websiteId = 111;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->product = $this->getMock('Magento\Catalog\Model\Product', ['__wakeup', 'getIdBySku'], [], '', false);
        $this->product->expects($this->any())
            ->method('getIdBySku')
            ->willReturn($this->productId);
        //getIdBySku
        $this->productFactory = $this->getMock('Magento\Catalog\Model\ProductFactory', ['create'], [], '', false);
        $this->productFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->product));

        $this->stock = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\Data\StockInterface',
            ['__wakeup'],
            '',
            false
        );
        $this->stockItem = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockItemInterface')
            ->setMethods(['setProductId', 'getData', 'addData', 'getItemId', 'getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockStatus = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\Data\StockStatusInterface',
            ['__wakeup'],
            '',
            false
        );

        $this->stockRegistryProvider = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface',
            ['getStock', 'getStockItem', 'getStockStatus'],
            '',
            false
        );
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStock')
            ->will($this->returnValue($this->stock));
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItem));
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStockStatus')
            ->will($this->returnValue($this->stockStatus));

        $this->stockItemRepository = $this->getMockForAbstractClass(
            '\Magento\CatalogInventory\Api\StockItemRepositoryInterface',
            ['save'],
            '',
            false
        );
        $this->stockItemRepository->expects($this->any())
            ->method('save')
            ->will($this->returnValue($this->stockItem));

        $this->stockRegistry = $this->objectManagerHelper->getObject(
            '\Magento\CatalogInventory\Model\StockRegistry',
            [
                'stockRegistryProvider' => $this->stockRegistryProvider,
                'productFactory' => $this->productFactory,
                'stockItemRepository' => $this->stockItemRepository
            ]
        );
    }

    protected function tearDown()
    {
        $this->stockRegistry = null;
    }

    public function testGetStock()
    {
        $this->assertEquals($this->stock, $this->stockRegistry->getStock($this->websiteId));
    }

    public function testGetStockItem()
    {
        $this->assertEquals($this->stockItem, $this->stockRegistry->getStockItem($this->productId, $this->websiteId));
    }

    public function testGetStockItemBySku()
    {
        $this->assertEquals(
            $this->stockItem,
            $this->stockRegistry->getStockItemBySku($this->productSku, $this->websiteId)
        );
    }

    public function testGetStockStatus()
    {
        $this->assertEquals(
            $this->stockStatus,
            $this->stockRegistry->getStockStatus($this->productId, $this->websiteId)
        );
    }

    public function testGetStockStatusBySku()
    {
        $this->assertEquals(
            $this->stockStatus,
            $this->stockRegistry->getStockStatus($this->productId, $this->websiteId)
        );
    }

    public function testUpdateStockItemBySku()
    {
        $itemId = 1;
        $this->stockItem->expects($this->once())->method('setProductId')->willReturnSelf();
        $this->stockItem->expects($this->once())->method('getData')->willReturn([]);
        $this->stockItem->expects($this->once())->method('addData')->willReturnSelf();
        $this->stockItem->expects($this->atLeastOnce())->method('getItemId')->willReturn($itemId);
        $this->assertEquals(
            $itemId,
            $this->stockRegistry->updateStockItemBySku($this->productSku, $this->stockItem)
        );
    }
}
