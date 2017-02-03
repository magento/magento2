<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $product = $objectManager->get('Magento\Catalog\Model\Product')->load(1);
        $expectedData = $product->getQuantityAndStockStatus();
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
        $this->assertArrayHasKey('stock_item', $actualData);
        $this->assertEquals($expectedData['is_in_stock'], $actualData['stock_item']['is_in_stock']);
        $this->assertEquals($expectedData['qty'], $actualData['stock_item']['qty']);
    }
}
