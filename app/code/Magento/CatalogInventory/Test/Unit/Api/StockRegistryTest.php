<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockRegistryTest extends TestCase
{
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

    protected $productId = 111;
    protected $productSku = 'simple';
    protected $websiteId = 111;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->product = $this->createPartialMock(Product::class, ['__wakeup', 'getIdBySku']);
        $this->product->expects($this->any())
            ->method('getIdBySku')
            ->willReturn($this->productId);
        //getIdBySku
        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->productFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->product);

        $this->stock = $this->getMockForAbstractClass(
            StockInterface::class,
            ['__wakeup'],
            '',
            false
        );
        $this->stockItem = $this->getMockBuilder(StockItemInterface::class)
            ->setMethods(['setProductId', 'getData', 'addData', 'getItemId', 'getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockStatus = $this->getMockForAbstractClass(
            StockStatusInterface::class,
            ['__wakeup'],
            '',
            false
        );

        $this->stockRegistryProvider = $this->getMockForAbstractClass(
            StockRegistryProviderInterface::class,
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
            StockItemRepositoryInterface::class,
            ['save'],
            '',
            false
        );
        $this->stockItemRepository->expects($this->any())
            ->method('save')
            ->willReturn($this->stockItem);

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
