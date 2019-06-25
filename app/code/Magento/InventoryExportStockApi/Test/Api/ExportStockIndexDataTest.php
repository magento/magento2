<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventorySales\Test\Api\OrderPlacementBase;

/**
 * Verify stock index data export for different product types.
 *
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/908874/scenarios/3042162
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/908874/scenarios/3042517
 */
class ExportStockIndexDataTest extends OrderPlacementBase
{
    const API_PATH = '/V1/inventory/dump-stock-index-data';
    const SERVICE_NAME = 'inventoryExportStockApiExportStockIndexDataV1';

    /**
     * Export stock index with simple product types - default stock and default website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     *
     * @return void
     */
    public function testExportStockDataSimpleProductTypesDefaultStockDefaultWebsite(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/base',
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];
        $this->assignStockToWebsite(1, 'base');
        $result = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['salesChannelCode' => 'base', 'salesChannelType' => 'website']);
        $this->verifyProducts(
            [
                ['sku' => 'SKU-1', 'qty' => 5.5, 'is_salable' => true],
                ['sku' => 'virtual-product', 'qty' => 100, 'is_salable' => true],
                ['sku' => 'downloadable-product', 'qty' => 100, 'is_salable' => true],
            ],
            $result
        );
    }

    /**
     * Export stock index with simple product types - additional stock and additional website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/product_virtual_source_item_on_additional_source.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/product_downloadable_source_item_on_additional_source.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @return void
     */
    public function testExportStockDataSimpleProductTypesAdditionalStockAdditionalWebsite(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/eu_website',
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];
        $this->assignProductsToWebsite(['downloadable-product', 'SKU-1', 'virtual-product'], 'eu_website');
        $result = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['salesChannelCode' => 'eu_website', 'salesChannelType' => 'website']);
        $this->verifyProducts(
            [
                ['sku' => 'SKU-1', 'qty' => 8.5, 'is_salable' => true],
                ['sku' => 'virtual-product', 'qty' => 100, 'is_salable' => true],
                ['sku' => 'downloadable-product', 'qty' => 100, 'is_salable' => true],
            ],
            $result
        );
    }

    /**
     * Export stock index with configurable product - default stock and default website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/default_stock_configurable_products.php
     *
     * @return void
     */
    public function testExportStockDataConfigurableProductDefaultStockDefaultWebsite(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/base',
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];
        $this->assignStockToWebsite(1, 'base');
        $result = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['salesChannelCode' => 'base', 'salesChannelType' => 'website']);
        $this->verifyProducts(
            [
                ['sku' => 'simple_10', 'qty' => 100, 'is_salable' => true],
                ['sku' => 'simple_20', 'qty' => 100, 'is_salable' => true],
                ['sku' => 'configurable_in_stock', 'qty' => 0, 'is_salable' => true],
            ],
            $result
        );
    }

    /**
     * Export stock index with configurable product - additional stock and additional website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @return void
     */
    public function testExportStockDataConfigurableProductAdditionalStockAdditionalWebsite(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/us_website',
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];
        $this->assignProductsToWebsite(['configurable', 'simple_10', 'simple_20'], 'us_website');
        $result = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['salesChannelCode' => 'us_website', 'salesChannelType' => 'website']);
        $this->verifyProducts(
            [
                ['sku' => 'simple_10', 'qty' => 100, 'is_salable' => true],
                ['sku' => 'simple_20', 'qty' => 100, 'is_salable' => true],
                ['sku' => 'configurable', 'qty' => 0, 'is_salable' => true],
            ],
            $result
        );
    }

    /**
     * Export stock index with grouped product - default stock and default website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     *
     * @return void
     */
    public function testExportStockDataGroupedProductDefaultStockDefaultWebsite(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/base',
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];
        $this->assignStockToWebsite(1, 'base');
        $result = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['salesChannelCode' => 'base', 'salesChannelType' => 'website']);
        $this->verifyProducts(
            [
                ['sku' => 'grouped_in_stock', 'qty' => 0, 'is_salable' => true],
                ['sku' => 'grouped_out_of_stock', 'qty' => 0, 'is_salable' => false],
                ['sku' => 'simple_11', 'qty' => 100, 'is_salable' => true],
                ['sku' => 'simple_22', 'qty' => 100, 'is_salable' => true],
            ],
            $result
        );
    }

    /**
     * Export stock index with grouped product - additional stock and additional website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/custom_stock_with_eu_website_grouped_products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/source_items_grouped_multiple.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @return void
     */
    public function testExportStockDataGroupedProductAdditionalStockAdditionalWebsite(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/us_website',
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];
        $this->assignProductsToWebsite(['grouped_in_stock', 'simple_11', 'simple_22'], 'us_website');
        $result = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['salesChannelCode' => 'us_website', 'salesChannelType' => 'website']);
        $this->verifyProducts(
            [
                ['sku' => 'grouped_in_stock', 'qty' => 0, 'is_salable' => true],
                ['sku' => 'simple_11', 'qty' => 100, 'is_salable' => true],
                ['sku' => 'simple_22', 'qty' => 100, 'is_salable' => true],
            ],
            $result
        );
    }

    /**
     * Export stock index with bundle product - default stock and default website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryBundleProduct/Test/_files/default_stock_bundle_products.php
     *
     * @return void
     */
    public function testExportStockDataBundleProductDefaultStockDefaultWebsite(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/base',
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];
        $this->assignStockToWebsite(1, 'base');
        $result = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['salesChannelCode' => 'base', 'salesChannelType' => 'website']);
        $this->verifyProducts(
            [
                ['sku' => 'simple', 'qty' => 22, 'is_salable' => true],
                ['sku' => 'simple-out-of-stock', 'qty' => 0, 'is_salable' => false],
                ['sku' => 'bundle-product-in-stock', 'qty' => 0, 'is_salable' => true],
                ['sku' => 'bundle-product-out-of-stock', 'qty' => 0, 'is_salable' => false],
            ],
            $result
        );
    }

    /**
     * Verify product export is correct.
     *
     * @param array $products
     * @param array $result
     * @return void
     */
    private function verifyProducts(array $products, array $result): void
    {
        $productsNum = count($products);
        $found = 0;
        foreach ($result as $resultProduct) {
            foreach ($products as $expectedProduct) {
                if ($resultProduct['sku'] === $expectedProduct['sku']) {
                    $found++;
                    self::assertEquals($expectedProduct, $resultProduct);
                }
            }
        }
        self::assertEquals($productsNum, $found);
    }
}
