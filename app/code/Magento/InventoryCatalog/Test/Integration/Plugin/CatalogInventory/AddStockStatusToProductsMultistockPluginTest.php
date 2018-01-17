<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\Plugin\CatalogInventory;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Helper\Stock as Helper;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests AddStockStatusToProductsMultistockPlugin::aroundAddStockStatusToProducts.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class AddStockStatusToProductsMultistockPluginTest extends TestCase
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * Tests AddStockStatusToProductsMultistockPlugin::aroundAddStockStatusToProducts for single stock.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @dataProvider addStockStatusToProductsSingleSourceDataProvider
     * @param array $productsData
     */
    public function testAddStockStatusToProductsSingleSource(array $productsData)
    {
        $this->addStockStatusToProducts($productsData);
    }

    /**
     * Tests AddStockStatusToProductsMultistockPlugin::aroundAddStockStatusToProducts for multiple stocks.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_link.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stores.php
     * @dataProvider addStockStatusToProductsMultiSourceDataProvider
     * @param string $storeCode
     * @param array $productsData
     */
    public function testAddStockStatusToProductsMultiSource(string $storeCode, array $productsData)
    {
        $this->storeManager->setCurrentStore($storeCode);
        $this->addStockStatusToProducts($productsData);
    }

    /**
     * Base test for AddStockStatusToProductsMultistockPlugin::aroundAddStockStatusToProducts.
     *
     * @param array $productsData
     */
    private function addStockStatusToProducts(array $productsData)
    {
        $this->productCollection->clear();
        $this->productCollection->addFieldToFilter('sku', ['in' => null]);
        $this->productCollection->load();
        foreach ($productsData as $productData) {
            $product = $this->productRepository->get($productData['sku']);
            $this->productCollection->addItem($product);
        }
        $this->helper->addStockStatusToProducts($this->productCollection);
        foreach ($productsData as $productData) {
            /** @var ProductInterface $product */
            $product = $this->productRepository->get($productData['sku']);
            $actual = $product->isSalable();
            self::assertEquals(
                $productData['expected'],
                $actual
            );
        }
    }

    /**
     * Data provider for testAddStockStatusToProductsSingleSource.
     *
     * @return array
     */
    public function addStockStatusToProductsSingleSourceDataProvider()
    {
        return [
            [
                [
                    ['sku' => 'SKU-1', 'expected' => 1],
                    ['sku' => 'SKU-2', 'expected' => 1],
                    ['sku' => 'SKU-3', 'expected' => 0],
                ],
            ],
        ];
    }

    /**
     * Data provider for testAddStockStatusToProductsMultiSource.
     *
     * @return array
     */
    public function addStockStatusToProductsMultiSourceDataProvider()
    {
        return [
            [
                'eu_store',
                [
                    ['sku' => 'SKU-1', 'expected' => 1],
                    ['sku' => 'SKU-2', 'expected' => 0],
                    ['sku' => 'SKU-3', 'expected' => 0],
                ],
            ],
            [
                'us_store',
                [
                    ['sku' => 'SKU-1', 'expected' => 0],
                    ['sku' => 'SKU-2', 'expected' => 1],
                    ['sku' => 'SKU-3', 'expected' => 0],
                ],
            ],
            [
                'global_store',
                [
                    ['sku' => 'SKU-1', 'expected' => 0],
                    ['sku' => 'SKU-2', 'expected' => 0],
                    ['sku' => 'SKU-3', 'expected' => 0],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->helper = Bootstrap::getObjectManager()->get(Helper::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->productCollection = Bootstrap::getObjectManager()->create(ProductCollection::class);
    }
}
