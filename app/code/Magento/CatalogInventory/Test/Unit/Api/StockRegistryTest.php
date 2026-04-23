<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Api;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\CatalogInventory\Model\Stock\Item;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockRegistryTest extends TestCase
{
    use MockCreationTrait;
    
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var StockRegistryProviderInterface|MockObject
     */
    protected $stockRegistryProvider;

    /**
     * @var StockInterface|MockObject
     */
    protected $stock;

    /**
     * @var StockItemInterface|MockObject
     */
    protected $stockItem;

    /**
     * @var StockStatusInterface|MockObject
     */
    protected $stockStatus;

    /**
     * @var ProductFactory|MockObject
     */
    protected $productFactory;

    /**
     * @var StockItemRepositoryInterface|MockObject
     */
    protected $stockItemRepository;

    /**
     * @var Product|MockObject
     */
    protected $product;

    private const PRODUCT_ID = 111;
    private const PRODUCT_SKU = 'simple';
    private const WEBSITE_ID = 111;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->product = $this->createPartialMock(Product::class, ['__wakeup', 'getIdBySku']);
        $this->product->method('getIdBySku')->willReturn(self::PRODUCT_ID);
        
        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->productFactory->method('create')->willReturn($this->product);

        $this->stock = $this->createMock(StockInterface::class);
        
        // Use concrete Item class instead of interface for proper type support
        $this->stockItem = $this->createPartialMockWithReflection(
            Item::class,
            ['getData', 'addData', 'getWebsiteId','setProductId', 'getItemId']
        );
        
        $this->stockStatus = $this->createMock(StockStatusInterface::class);

        $this->stockRegistryProvider = $this->createMock(StockRegistryProviderInterface::class);
        $this->stockRegistryProvider->method('getStock')->willReturn($this->stock);
        $this->stockRegistryProvider->method('getStockItem')->willReturn($this->stockItem);
        $this->stockRegistryProvider->method('getStockStatus')->willReturn($this->stockStatus);

        $this->stockItemRepository = $this->createMock(StockItemRepositoryInterface::class);
        $this->stockItemRepository->method('save')->willReturn($this->stockItem);

        $this->stockRegistry = $this->objectManagerHelper->getObject(
            StockRegistry::class,
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
        $this->assertEquals($this->stock, $this->stockRegistry->getStock(self::WEBSITE_ID));
    }

    public function testGetStockItem()
    {
        $this->assertEquals($this->stockItem, $this->stockRegistry->getStockItem(self::PRODUCT_ID, self::WEBSITE_ID));
    }

    public function testGetStockItemBySku()
    {
        $this->assertEquals(
            $this->stockItem,
            $this->stockRegistry->getStockItemBySku(self::PRODUCT_SKU, self::WEBSITE_ID)
        );
    }

    public function testGetStockStatus()
    {
        $this->assertEquals(
            $this->stockStatus,
            $this->stockRegistry->getStockStatus(self::PRODUCT_ID, self::WEBSITE_ID)
        );
    }

    public function testGetStockStatusBySku()
    {
        $this->assertEquals(
            $this->stockStatus,
            $this->stockRegistry->getStockStatus(self::PRODUCT_ID, self::WEBSITE_ID)
        );
    }

    public function testUpdateStockItemBySku()
    {
        $itemId = 1;
        $testData = ['test_key' => 'test_value'];
        
        $this->stockItem->method('getWebsiteId')->willReturn(null);
        $this->stockItem->method('getData')->willReturn($testData);
        
        $this->stockItem->method('getItemId')->willReturn($itemId);
        $this->stockItem->method('getData')->willReturn($testData);
        
        $this->assertEquals(
            $itemId,
            $this->stockRegistry->updateStockItemBySku(self::PRODUCT_SKU, $this->stockItem)
        );
    }
}
