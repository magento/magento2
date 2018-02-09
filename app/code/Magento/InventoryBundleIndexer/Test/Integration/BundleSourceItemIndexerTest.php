<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Inventory\Model\GetStockItemDataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class BundleSourceItemIndexerTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemDataInterface::class);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleIndexer/Test/_files/bundle_product_with_child_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleIndexer/Test/_files/bundle_product_with_all_children_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleIndexer/Test/_files/bundle_product_with_all_children_in_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleIndexer/Test/_files/source_items_bundle.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    // @codingStandardsIgnoreEnd
    public function testBundleProductReindex()
    {
        $childrenInStockData = $this->getStockItemData->execute('bundle-product-with-all-children-in-stock', 10);
        $childrenOutOfStockData = $this->getStockItemData->execute('bundle-product-with-all-children-out-of-stock', 10);
        $childOutOfStockData = $this->getStockItemData->execute('bundle-product-with-child-out-of-stock', 10);

        self::assertEquals(1, $childrenInStockData['is_salable']);
        self::assertEquals(0, $childrenOutOfStockData['is_salable']);
        self::assertEquals(1, $childOutOfStockData['is_salable']);
    }
}
