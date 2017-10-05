<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Test\Api;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryCatalog\Api\DefaultStockRepositoryInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class GetDefaultStockTest
 */
class GetDefaultStockTest extends WebapiAbstract
{
    /**
     * Test that default Stock is present after installation
     */
    public function testGetDefaultSource()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/stock/' . DefaultStockRepositoryInterface::DEFAULT_STOCK,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'inventoryApiStockRepositoryV1',
                'operation' => 'inventoryApiStockRepositoryV1Get',
            ],
        ];
        if (self::ADAPTER_REST == TESTS_WEB_API_ADAPTER) {
            $stock = $this->_webApiCall($serviceInfo);
        } else {
            $stock = $this->_webApiCall($serviceInfo, ['stockId' => DefaultStockRepositoryInterface::DEFAULT_STOCK]);
        }
        $this->assertEquals(DefaultStockRepositoryInterface::DEFAULT_STOCK, $stock[StockInterface::STOCK_ID]);
    }
}
