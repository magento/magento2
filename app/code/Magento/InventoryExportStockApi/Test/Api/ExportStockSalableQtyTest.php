<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Test\Api;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventorySales\Test\Api\OrderPlacementBase;

/**
 * Salable qty export tests for different types of products.
 *
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/908874/scenarios/3042272
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/908874/scenarios/3042336
 */
class ExportStockSalableQtyTest extends OrderPlacementBase
{
    const API_PATH = '/V1/inventory/export-stock-salable-qty';
    const SERVICE_NAME = 'inventoryExportStockApiExportStockSalableQtyV1';

    /**
     * Verify salable qty export with reservations simple product types - default stock, default website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @dataProvider simpleProductTypesDataProvider()
     * @param array $filters
     * @return void
     */
    public function testExportSimpleProductTypesWithReservationsDefaultWebsiteDefaultStock(array $filters): void
    {
        $this->_markTestAsRestOnly();
        $this->assignStockToWebsite(1, 'base');
        $simpleProductSKU = 'SKU-1';
        $virtualProductSKU = 'virtual-product';
        $downloadableProductSKU = 'downloadable-product';
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [['filters' => $filters]]
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(
                    "%s/%s/%s?%s",
                    self::API_PATH,
                    'website',
                    'base',
                    http_build_query($requestData)
                ),
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
        ];
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $virtualProductSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
                [
                    'sku' => $downloadableProductSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
                [
                    'sku' => $simpleProductSKU,
                    'qty' => 5.5,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->createCustomerCart();
        $this->addProduct($simpleProductSKU);
        $this->addProduct($virtualProductSKU);
        $this->addProduct($downloadableProductSKU);
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $virtualProductSKU,
                    'qty' => 99,
                    'is_salable' => true,
                ],
                [
                    'sku' => $downloadableProductSKU,
                    'qty' => 99,
                    'is_salable' => true,
                ],
                [
                    'sku' => $simpleProductSKU,
                    'qty' => 4.5,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->cancelOrder($orderId);
    }

    /**
     * Verify salable qty export with reservations simple product types - additional stock, additional website.
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
     * @dataProvider simpleProductTypesDataProvider()
     * @param array $filters
     * @return void
     */
    public function testExportSimpleProductTypesWithReservationsAdditionalWebsiteAdditionalStock(array $filters): void
    {
        $this->_markTestAsRestOnly();
        $this->setStoreView('store_for_eu_website');
        $downloadableSKU = 'downloadable-product';
        $simpleSKU = 'SKU-1';
        $virtualSKU = 'virtual-product';
        $this->assignProductsToWebsite([$downloadableSKU, $simpleSKU, $virtualSKU], 'eu_website');
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [['filters' => $filters]]
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(
                    "%s/%s/%s?%s",
                    self::API_PATH,
                    'website',
                    'eu_website',
                    http_build_query($requestData)
                ),
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
        ];
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $virtualSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
                [
                    'sku' => $downloadableSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
                [
                    'sku' => $simpleSKU,
                    'qty' => 8.5,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->createCustomerCart();
        $this->addProduct($simpleSKU);
        $this->addProduct($virtualSKU);
        $this->addProduct($downloadableSKU);
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $virtualSKU,
                    'qty' => 99,
                    'is_salable' => true,
                ],
                [
                    'sku' => $downloadableSKU,
                    'qty' => 99,
                    'is_salable' => true,
                ],
                [
                    'sku' => $simpleSKU,
                    'qty' => 7.5,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->cancelOrder($orderId);
    }

    /**
     * Export salable qty for simple product types data provider.
     *
     * @return array
     */
    public function simpleProductTypesDataProvider()
    {
        return [
            [
                'filters' => [
                    [
                        'field' => SourceItemInterface::SKU,
                        'value' => 'downloadable-product',
                        'condition_type' => 'eq',
                    ],
                    [
                        'field' => SourceItemInterface::SKU,
                        'value' => 'virtual-product',
                        'condition_type' => 'eq',
                    ],
                    [
                        'field' => SourceItemInterface::SKU,
                        'value' => 'SKU-1',
                        'condition_type' => 'eq',
                    ],
                ],
            ]
        ];
    }

    /**
     * Verify salable qty export with reservations configurable product - default stock, default website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/default_stock_configurable_products.php
     *
     * @return void
     */
    public function testExportConfigurableWithReservationsDefaultWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->assignStockToWebsite(1, 'base');
        $configurableSKU = 'configurable_in_stock';
        $firstOptionSKU = 'simple_10';
        $secondsOptionSKU = 'simple_20';
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $configurableSKU,
                                'condition_type' => 'eq',
                            ],
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $firstOptionSKU,
                                'condition_type' => 'eq',
                            ],
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $secondsOptionSKU,
                                'condition_type' => 'eq',
                            ],
                        ]
                    ]
                ]
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(
                    "%s/%s/%s?%s",
                    self::API_PATH,
                    'website',
                    'base',
                    http_build_query($requestData)
                ),
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
        ];
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $configurableSKU,
                    'qty' => 0,
                    'is_salable' => true,
                ],
                [
                    'sku' => $firstOptionSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
                [
                    'sku' => $secondsOptionSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->createCustomerCart();
        $this->addConfigurableProduct($configurableSKU);
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $configurableSKU,
                    'qty' => 0,
                    'is_salable' => true,
                ],
                [
                    'sku' => $firstOptionSKU,
                    'qty' => 99,
                    'is_salable' => true,
                ],
                [
                    'sku' => $secondsOptionSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->cancelOrder($orderId);
    }

    /**
     * Verify salable qty export with reservations configurable product - additional stock, additional website.
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
    public function testExportConfigurableWithReservationsAdditionalWebsiteAdditionalStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->setStoreView('store_for_us_website');
        $configurableSKU = 'configurable';
        $firstOptionSKU = 'simple_10';
        $secondsOptionSKU = 'simple_20';
        $this->assignProductsToWebsite([$configurableSKU, $firstOptionSKU, $secondsOptionSKU], 'us_website');
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $configurableSKU,
                                'condition_type' => 'eq',
                            ],
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $firstOptionSKU,
                                'condition_type' => 'eq',
                            ],
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $secondsOptionSKU,
                                'condition_type' => 'eq',
                            ],
                        ]
                    ]
                ]
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(
                    "%s/%s/%s?%s",
                    self::API_PATH,
                    'website',
                    'us_website',
                    http_build_query($requestData)
                ),
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
        ];
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $configurableSKU,
                    'qty' => 0,
                    'is_salable' => true,
                ],
                [
                    'sku' => $firstOptionSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
                [
                    'sku' => $secondsOptionSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->createCustomerCart();
        $this->addConfigurableProduct($configurableSKU);
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $configurableSKU,
                    'qty' => 0,
                    'is_salable' => true,
                ],
                [
                    'sku' => $firstOptionSKU,
                    'qty' => 99,
                    'is_salable' => true,
                ],
                [
                    'sku' => $secondsOptionSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->cancelOrder($orderId);
    }

    /**
     * Verify salable qty export with reservations grouped product - default stock, default website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     *
     * @return void
     */
    public function testExportGroupedWithReservationsDefaultWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->assignStockToWebsite(1, 'base');
        $groupedSKU = 'grouped_in_stock';
        $firstOptionSKU = 'simple_11';
        $secondsOptionSKU = 'simple_22';
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $groupedSKU,
                                'condition_type' => 'eq',
                            ],
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $firstOptionSKU,
                                'condition_type' => 'eq',
                            ],
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $secondsOptionSKU,
                                'condition_type' => 'eq',
                            ],
                        ]
                    ]
                ]
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(
                    "%s/%s/%s?%s",
                    self::API_PATH,
                    'website',
                    'base',
                    http_build_query($requestData)
                ),
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
        ];
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $groupedSKU,
                    'qty' => 0,
                    'is_salable' => true,
                ],
                [
                    'sku' => $firstOptionSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
                [
                    'sku' => $secondsOptionSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->createCustomerCart();
        $this->addProduct($groupedSKU);
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $groupedSKU,
                    'qty' => 0,
                    'is_salable' => true,
                ],
                [
                    'sku' => $firstOptionSKU,
                    'qty' => 99,
                    'is_salable' => true,
                ],
                [
                    'sku' => $secondsOptionSKU,
                    'qty' => 99,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->cancelOrder($orderId);
    }

    /**
     * Verify salable qty export with reservations grouped product - additional stock, additional website.
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
    public function testExportGroupedWithReservationsAdditionalWebsiteAdditionalStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->setStoreView('store_for_us_website');
        $groupedSKU = 'grouped_in_stock';
        $firstOptionSKU = 'simple_11';
        $secondsOptionSKU = 'simple_22';
        $this->assignProductsToWebsite([$groupedSKU, $firstOptionSKU, $secondsOptionSKU], 'us_website');
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $groupedSKU,
                                'condition_type' => 'eq',
                            ],
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $firstOptionSKU,
                                'condition_type' => 'eq',
                            ],
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $secondsOptionSKU,
                                'condition_type' => 'eq',
                            ],
                        ]
                    ]
                ]
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(
                    "%s/%s/%s?%s",
                    self::API_PATH,
                    'website',
                    'us_website',
                    http_build_query($requestData)
                ),
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
        ];
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $groupedSKU,
                    'qty' => 0,
                    'is_salable' => true,
                ],
                [
                    'sku' => $firstOptionSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
                [
                    'sku' => $secondsOptionSKU,
                    'qty' => 100,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->createCustomerCart();
        $this->addProduct($groupedSKU);
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $groupedSKU,
                    'qty' => 0,
                    'is_salable' => true,
                ],
                [
                    'sku' => $firstOptionSKU,
                    'qty' => 99,
                    'is_salable' => true,
                ],
                [
                    'sku' => $secondsOptionSKU,
                    'qty' => 99,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->cancelOrder($orderId);
    }

    /**
     * Verify salable qty export with reservations bundle product - default stock, default website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     *
     * @return void
     */
    public function testExportBundleWithReservationsDefaultWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->assignStockToWebsite(1, 'base');
        $bundleSKU = 'bundle-product';
        $simple = 'simple';
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $bundleSKU,
                                'condition_type' => 'eq',
                            ],
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $simple,
                                'condition_type' => 'eq',
                            ],
                        ]
                    ]
                ]
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(
                    "%s/%s/%s?%s",
                    self::API_PATH,
                    'website',
                    'base',
                    http_build_query($requestData)
                ),
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
        ];
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $bundleSKU,
                    'qty' => 0,
                    'is_salable' => true,
                ],
                [
                    'sku' => $simple,
                    'qty' => 22,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->createCustomerCart();
        $this->addBundleProduct($bundleSKU);
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $result = $this->_webApiCall($serviceInfo);
        $this->verifyProducts(
            [
                [
                    'sku' => $bundleSKU,
                    'qty' => 0,
                    'is_salable' => true,
                ],
                [
                    'sku' => $simple,
                    'qty' => 20,
                    'is_salable' => true,
                ],
            ],
            $result['items']
        );
        $this->cancelOrder($orderId);
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
