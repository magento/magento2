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
class StockRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistryProvider;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stock;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockItem;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockStatusInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockStatus;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $product;

    protected $productId = 111;
    protected $productSku = 'simple';
    protected $websiteId = 111;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['__wakeup', 'getIdBySku']);
        $this->product->expects($this->any())
            ->method('getIdBySku')
            ->willReturn($this->productId);
        //getIdBySku
        $this->productFactory = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);
        $this->productFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->product);

        $this->stock = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\Data\StockInterface::class,
            ['__wakeup'],
            '',
            false
        );
        $this->stockItem = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->setMethods(['setProductId', 'getData', 'addData', 'getItemId', 'getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockStatus = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\Data\StockStatusInterface::class,
            ['__wakeup'],
            '',
            false
        );

        $this->stockRegistryProvider = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface::class,
            ['getStock', 'getStockItem', 'getStockStatus'],
            '',
            false
        );
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStock')
            ->willReturn($this->stock);
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItem);
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStockStatus')
            ->willReturn($this->stockStatus);

        $this->stockItemRepository = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\StockItemRepositoryInterface::class,
            ['save'],
            '',
            false
        );
        $this->stockItemRepository->expects($this->any())
            ->method('save')
            ->willReturn($this->stockItem);

        $this->stockRegistry = $this->objectManagerHelper->getObject(
            \Magento\CatalogInventory\Model\StockRegistry::class,
            [
                'stockRegistryProvider' => $this->stockRegistryProvider,
                'productFactory' => $this->productFactory,
                'stockItemRepository' => $this->stockItemRepository
            ]
        );
    }

    protected function tearDown(): void
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
