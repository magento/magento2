<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class StockStatusTest
 */
class StockStatusTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/stockStatuses';

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetProductStockStatus()
    {
        $productSku = 'simple';
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->get(\Magento\Catalog\Model\Product::class)->load(1);
        $expectedData = $product->getQuantityAndStockStatus();
        $actualData = $this->getProductStockStatus($productSku);
        $this->assertArrayHasKey('stock_item', $actualData);
        $this->assertEquals($expectedData['is_in_stock'], $actualData['stock_item']['is_in_stock']);
        $this->assertEquals($expectedData['qty'], $actualData['stock_item']['qty']);
    }

    private function getProductStockStatus($productSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$productSku",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogInventoryStockRegistryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'catalogInventoryStockRegistryV1GetStockStatusBySku',
            ],
        ];

        $requestData = ['productSku' => $productSku];
        $actualData = $this->_webApiCall($serviceInfo, $requestData);

        return $actualData;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_sku_with_slash.php
     */
    public function testGetProductStockStatusBySkuWithSlashes()
    {
        $productSku = [
            'rest' => 'sku%252fwith%252fslashes',
            'soap' => 'sku%2fwith%2fslashes'
        ];
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->get(\Magento\Catalog\Model\Product::class)->load(1);
        $expectedData = $product->getQuantityAndStockStatus();
        $actualData = $this->getProductStockStatus($productSku[TESTS_WEB_API_ADAPTER]);
        $this->assertArrayHasKey('stock_item', $actualData);
        $this->assertEquals($expectedData['is_in_stock'], $actualData['stock_item']['is_in_stock']);
        $this->assertEquals($expectedData['qty'], $actualData['stock_item']['qty']);
    }
}
