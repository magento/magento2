<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Test\Integration;

use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;
use Magento\InventorySales\Model\GetStockItemDataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SourceItemIndexerTest extends TestCase
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
     * @var Selection
     */
    private $selection;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemDataInterface::class);
        $this->selection = Bootstrap::getObjectManager()->get(Selection::class);
        $this->getProductIdsBySkus = Bootstrap::getObjectManager()->get(GetProductIdsBySkusInterface::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items_eu_stock_only.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleIndexer/Test/_files/bundle_product_eu_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @return void
     */
    public function testReindexWithAllChildrenInStock()
    {
        $bundleStockItemData = $this->getStockItemData->execute('bundle-product-eu-website', 10);

        self::assertEquals(1, $bundleStockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items_eu_stock_only.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleIndexer/Test/_files/bundle_product_eu_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @return void
     */
    public function testReindexWithOneChildOutOfStock()
    {
        $this->makeChildrenOutOfStock(1);
        $bundleStockItemData = $this->getStockItemData->execute('bundle-product-eu-website', 10);

        self::assertEquals(1, $bundleStockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items_eu_stock_only.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleIndexer/Test/_files/bundle_product_eu_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @return void
     */
    public function testReindexWithAllChildrenOutOfStock()
    {
        $this->makeChildrenOutOfStock(3);
        $bundleStockItemData = $this->getStockItemData->execute('bundle-product-eu-website', 10);

        self::assertEquals(0, $bundleStockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items_eu_stock_only.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleIndexer/Test/_files/bundle_product_eu_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @return void
     */
    public function testReindexWhenSaveParent()
    {
        $bundleSku = 'bundle-product-eu-website';

        $this->makeChildrenOutOfStock(2);
        $bundleStockItemData = $this->getStockItemData->execute($bundleSku, 10);
        self::assertEquals(1, (bool)$bundleStockItemData[GetStockItemDataInterface::IS_SALABLE]);

        //unassign only in stock product from bundle to make it out of stock
        $bundleProduct = $this->productRepository->get($bundleSku, true, null, true);
        $productLinks = $bundleProduct->getExtensionAttributes()->getBundleProductOptions()[0]->getProductLinks();
        $unassignedLink = $productLinks[2];
        unset($productLinks[2]);
        $bundleProduct->getExtensionAttributes()->getBundleProductOptions()[0]->setProductLinks($productLinks);
        $this->productRepository->save($bundleProduct);
        $bundleStockItemData = $this->getStockItemData->execute($bundleSku, 10);
        self::assertEquals(0, (bool)$bundleStockItemData[GetStockItemDataInterface::IS_SALABLE]);

        //assign product in stock to make bundle in stock
        $unassignedLink->setId(null);
        $productLinks[2] = $unassignedLink;
        $bundleProduct->getExtensionAttributes()->getBundleProductOptions()[0]->setProductLinks($productLinks);
        $this->productRepository->save($bundleProduct);
        $bundleStockItemData = $this->getStockItemData->execute($bundleSku, 10);
        self::assertEquals(1, (bool)$bundleStockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }

    /**
     * @param int $childrenQty
     * @return void
     */
    private function makeChildrenOutOfStock(int $childrenQty)
    {
        $ids = $this->getProductIdsBySkus->execute(['bundle-product-eu-website']);
        $id = reset($ids);

        $childrenIds = $this->selection->getChildrenIds($id)[0];
        foreach ($childrenIds as $childId) {
            if ($childrenQty === 0) {
                break;
            }
            $child = $this->productRepository->getById($childId);

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(SourceItemInterface::SKU, $child->getSku())
                ->create();
            $items = $this->sourceItemRepository->getList($searchCriteria)->getItems();
            $sourceItem = reset($items);
            $sourceItem->setQuantity(0);
            $sourceItem->setStatus(0);

            $this->sourceItemsSave->execute([$sourceItem]);
            $childrenQty--;
        }
    }
}
