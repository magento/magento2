<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundle\Test\Integration\CatalogInventory\Model\Spi\StockRegistryProvider;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test StockRegistryInterface::getStockStatus() for bundle product type.
 */
class GetStockStatusTest extends TestCase
{
    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockForCurrentWebsite;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockItemCriteriaInterface
     */
    private $stockItemCriteria;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->stockRegistryProvider = Bootstrap::getObjectManager()->get(StockRegistryProviderInterface::class);
        $this->getProductIdsBySkus = Bootstrap::getObjectManager()->get(GetProductIdsBySkusInterface::class);
        $this->getStockForCurrentWebsite = Bootstrap::getObjectManager()->get(GetStockIdForCurrentWebsite::class);
        $this->stockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
        $this->stockItemCriteria = Bootstrap::getObjectManager()->create(StockItemCriteriaInterface::class);
    }

    /**
     * Check, bundle has correct stock status configuration on default source.
     *
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testGetStatusOnDefaultSource()
    {
        $stockId = $this->getStockForCurrentWebsite->execute();
        $productIds = $this->getProductIdsBySkus->execute(['bundle-product']);
        $productId = reset($productIds);

        //Check product with 'In Stock' status.
        $stockStatus = $this->stockRegistryProvider->getStockStatus($productId, 1);
        $this->assertInstanceOf(StockStatusInterface::class, $stockStatus);
        $this->assertEquals(1, $stockStatus->getStockStatus());
        $this->assertEquals(0, $stockStatus->getQty());
        $this->assertEquals($stockId, $stockStatus->getStockId());
        $this->assertEquals($productId, $stockStatus->getProductId());

        $this->setProductsOutOfStock((int)$productId);

        //Check product with 'Out of Stock' status.
        $stockStatus = $this->stockRegistryProvider->getStockStatus($productId, 1);
        $this->assertInstanceOf(StockStatusInterface::class, $stockStatus);
        $this->assertEquals(0, $stockStatus->getStockStatus());
        $this->assertEquals(0, $stockStatus->getQty());
        $this->assertEquals($stockId, $stockStatus->getStockId());
        $this->assertEquals($productId, $stockStatus->getProductId());
    }

    /**
     * Check, bundle product has correct stock status on custom source.
     *
     * @return void
     */
    public function testGetStatusOnCustomSource()
    {
        $this->markTestSkipped('Bundle product type not supported on custom source');
    }

    /**
     * Set bundle to 'Out of Stock'.
     *
     * @param int $productId
     * @return void
     */
    private function setProductsOutOfStock(int $productId)
    {
        $this->stockItemCriteria->setProductsFilter($productId);
        $stockItems = $this->stockItemRepository->getList($this->stockItemCriteria)->getItems();
        $configurableStockItem = reset($stockItems);
        $configurableStockItem->setIsInStock(false);
        $this->stockItemRepository->save($configurableStockItem);
    }
}
