<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundle\Test\Integration\CatalogInventory\Api\StockRegistry;

use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests StockRegistryInterface::getProductStockStatus() for bundle product type.
 */
class GetProductStockStatusTest extends TestCase
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

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
        $this->stockRegistry = Bootstrap::getObjectManager()->get(StockRegistryInterface::class);
        $this->getProductIdsBySkus = Bootstrap::getObjectManager()->get(GetProductIdsBySkusInterface::class);
        $this->stockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
        $this->stockItemCriteria = Bootstrap::getObjectManager()->create(StockItemCriteriaInterface::class);
    }

    /**
     * Check, bundle product has correct stock status on default source.
     *
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testGetStatusOnDefaultSource()
    {
        $productIds = $this->getProductIdsBySkus->execute(['bundle-product']);
        $productId = reset($productIds);

        //Check product with 'In Stock' status.
        $this->assertEquals(1, $this->stockRegistry->getProductStockStatus($productId));

        $this->setProductsOutOfStock((int)$productId);

        //Check product with 'Out of Stock' status.
        $this->assertEquals(0, $this->stockRegistry->getProductStockStatus($productId));
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
