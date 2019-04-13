<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\IsProductSalableForRequestedQty;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsAnySourceItemInStockCondition;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsAnySourceItemInStockConditionTest extends TestCase
{
    /**
     * @var IsAnySourceItemInStockCondition
     */
    private $isAnySourceInStockCondition;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->isAnySourceInStockCondition = $objectManager->get(
            IsAnySourceItemInStockCondition::class
        );
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->stockItemCriteriaFactory = $objectManager->get(StockItemCriteriaInterfaceFactory::class);
        $this->stockItemRepository = $objectManager->get(StockItemRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items_out_of_stock.php
     *
     * @magentoDbIsolation disabled
     */
    public function testSourceItemsAreOutOfStock()
    {
        $product = $this->productRepository->get('SKU-1');
        $stockItemSearchCriteria = $this->stockItemCriteriaFactory->create();
        $stockItemSearchCriteria->setProductsFilter($product->getId());
        $stockItemsCollection = $this->stockItemRepository->getList($stockItemSearchCriteria);

        /** @var StockItemInterface $legacyStockItem */
        $legacyStockItem = current($stockItemsCollection->getItems());
        $legacyStockItem->setBackorders(1);
        $legacyStockItem->setUseConfigBackorders(0);
        $this->stockItemRepository->save($legacyStockItem);
        $this->assertFalse($this->isAnySourceInStockCondition->execute('SKU-1', 10, 1)->isSalable());
    }
}
