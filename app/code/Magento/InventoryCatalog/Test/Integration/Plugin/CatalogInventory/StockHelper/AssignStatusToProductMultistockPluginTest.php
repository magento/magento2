<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\Plugin\CatalogInventory\StockHelper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Helper\Stock as Helper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests AssignStatusToProductMultistockPlugin::assignStatusToProduct.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class AssignStatusToProductMultistockPluginTest extends TestCase
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
     * Tests AssignStatusToProductMultistockPlugin::assignStatusToProduct for single stock.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @dataProvider assignStatusToProductSingleSourceDataProvider
     * @param array $productsData
     */
    public function testAssignStatusToProductSingleSource(array $productsData)
    {
        $this->assignStatusToProduct($productsData);
    }

    /**
     * Tests AssignStatusToProductMultistockPlugin::assignStatusToProduct for multiple stocks.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_link.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stores.php
     * @dataProvider assignStatusToProductMultiSourceDataProvider
     * @param string $storeCode
     * @param array $productsData
     */
    public function testAssignStatusToProductMultiSource(string $storeCode, array $productsData)
    {
        $this->storeManager->setCurrentStore($storeCode);
        $this->assignStatusToProduct($productsData);
    }

    /**
     * Base test for AssignStatusToProductMultistockPlugin::assignStatusToProduct.
     *
     * @param array $productsData
     */
    private function assignStatusToProduct(array $productsData)
    {
        foreach ($productsData as $productData) {
            /** @var ProductInterface $product */
            $product = $this->productRepository->get($productData['sku']);
            $this->helper->assignStatusToProduct($product, $productData['status']);
            $actual = $product->isSalable();
            self::assertEquals(
                $productData['expected'],
                $actual
            );
        }
    }

    /**
     * Data provider for testAssignStatusToProductSingleSource.
     *
     * @return array
     */
    public function assignStatusToProductSingleSourceDataProvider()
    {
        return [
            [
                [
                    ['sku' => 'SKU-1', 'status' => null, 'expected' => 1],
                    ['sku' => 'SKU-2', 'status' => null, 'expected' => 1],
                    ['sku' => 'SKU-3', 'status' => null, 'expected' => 0],
                ],
            ],
            [
                [
                    ['sku' => 'SKU-1', 'status' => 1, 'expected' => 1],
                    ['sku' => 'SKU-2', 'status' => 1, 'expected' => 1],
                    ['sku' => 'SKU-3', 'status' => 1, 'expected' => 1],
                ],
            ],
            [
                [
                    ['sku' => 'SKU-1', 'status' => 0, 'expected' => 0],
                    ['sku' => 'SKU-2', 'status' => 0, 'expected' => 0],
                    ['sku' => 'SKU-3', 'status' => 0, 'expected' => 0],
                ],
            ],
        ];
    }

    /**
     * Data provider for testAssignStatusToProductMultiSource.
     *
     * @return array
     */
    public function assignStatusToProductMultiSourceDataProvider()
    {
        return [
            [
                'eu_store',
                [
                    ['sku' => 'SKU-1', 'status' => null, 'expected' => 1],
                    ['sku' => 'SKU-2', 'status' => null, 'expected' => 0],
                    ['sku' => 'SKU-3', 'status' => null, 'expected' => 0],
                ],
            ],
            [
                'us_store',
                [
                    ['sku' => 'SKU-1', 'status' => null, 'expected' => 0],
                    ['sku' => 'SKU-2', 'status' => null, 'expected' => 1],
                    ['sku' => 'SKU-3', 'status' => null, 'expected' => 0],
                ],
            ],
            [
                'global_store',
                [
                    ['sku' => 'SKU-1', 'status' => null, 'expected' => 0],
                    ['sku' => 'SKU-2', 'status' => null, 'expected' => 0],
                    ['sku' => 'SKU-3', 'status' => null, 'expected' => 0],
                ],
            ],
            [
                'eu_store',
                [
                    ['sku' => 'SKU-1', 'status' => 1, 'expected' => 1],
                    ['sku' => 'SKU-2', 'status' => 1, 'expected' => 1],
                    ['sku' => 'SKU-3', 'status' => 1, 'expected' => 1],
                ],
            ],
            [
                'eu_store',
                [
                    ['sku' => 'SKU-1', 'status' => 0, 'expected' => 0],
                    ['sku' => 'SKU-2', 'status' => 0, 'expected' => 0],
                    ['sku' => 'SKU-3', 'status' => 0, 'expected' => 0],
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
    }
}
