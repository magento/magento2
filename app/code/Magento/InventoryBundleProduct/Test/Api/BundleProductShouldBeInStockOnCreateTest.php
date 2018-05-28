<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleProduct\Test\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Bundle\Api\Data\LinkInterface;

class BundleProductShouldBeInStockOnCreateTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';
    const BUNDLE_PRODUCT_SKU = 'SKU-1-test-product-bundle';

    /**
     * Execute per test cleanup
     */
    public function tearDown()
    {
        $resourcePath = self::RESOURCE_PATH . '/' . self::BUNDLE_PRODUCT_SKU;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'deleteById',
            ],
        ];
        $requestData = ["sku" => self::BUNDLE_PRODUCT_SKU];
        $this->_webApiCall($serviceInfo, $requestData);

        parent::tearDown();
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     */
    public function testIsBundleProductWithSimpleProductInStockAfterCreate()
    {
        $bundleProductOptions = [
            [
                "title" => "Test simple",
                "type" => "select",
                "required" => false,
                "product_links" => [
                    [
                        "sku" => 'SKU-1',
                        "qty" => 1.5,
                        'is_default' => false,
                        'price' => 1.0,
                        'price_type' => LinkInterface::PRICE_TYPE_FIXED,
                    ],
                ],
            ],
        ];
        $product = [
            "sku" => self::BUNDLE_PRODUCT_SKU,
            "name" => 'bundle product',
            "type_id" => Type::TYPE_BUNDLE,
            "price" => 50,
            'attribute_set_id' => 4,
            "custom_attributes" => [
                "price_type" => [
                    'attribute_code' => 'price_type',
                    'value' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
                ],
                "price_view" => [
                    "attribute_code" => "price_view",
                    "value" => "1",
                ],
            ],
            "extension_attributes" => [
                "bundle_product_options" => $bundleProductOptions,
                'stock_item' => $this->getStockItemData()
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals(self::BUNDLE_PRODUCT_SKU, $response[ProductInterface::SKU]);
        $this->assertEquals(1, $response['extension_attributes']['stock_item']['is_in_stock']);
    }

    /**
     * @return array
     */
    private function getStockItemData()
    {
        return [
            StockItemInterface::IS_IN_STOCK => 1,
            StockItemInterface::QTY => 100500,
            StockItemInterface::IS_QTY_DECIMAL => 1,
            StockItemInterface::SHOW_DEFAULT_NOTIFICATION_MESSAGE => 0,
            StockItemInterface::USE_CONFIG_MIN_QTY => 0,
            StockItemInterface::USE_CONFIG_MIN_SALE_QTY => 0,
            StockItemInterface::MIN_QTY => 1,
            StockItemInterface::MIN_SALE_QTY => 1,
            StockItemInterface::MAX_SALE_QTY => 100,
            StockItemInterface::USE_CONFIG_MAX_SALE_QTY => 0,
            StockItemInterface::USE_CONFIG_BACKORDERS => 0,
            StockItemInterface::BACKORDERS => 0,
            StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY => 0,
            StockItemInterface::NOTIFY_STOCK_QTY => 0,
            StockItemInterface::USE_CONFIG_QTY_INCREMENTS => 0,
            StockItemInterface::QTY_INCREMENTS => 0,
            StockItemInterface::USE_CONFIG_ENABLE_QTY_INC => 0,
            StockItemInterface::ENABLE_QTY_INCREMENTS => 0,
            StockItemInterface::USE_CONFIG_MANAGE_STOCK => 1,
            StockItemInterface::MANAGE_STOCK => 1,
            StockItemInterface::LOW_STOCK_DATE => null,
            StockItemInterface::IS_DECIMAL_DIVIDED => 0,
            StockItemInterface::STOCK_STATUS_CHANGED_AUTO => 0,
        ];
    }
}
