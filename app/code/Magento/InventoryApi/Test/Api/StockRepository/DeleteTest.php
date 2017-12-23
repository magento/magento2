<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\StockRepository;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class DeleteTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stock';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     */
    public function testDeleteById()
    {
        $stockIdToDelete = 10;
        $expectedStocksAfterDeleting = [
            [
                StockInterface::STOCK_ID => 1,
                StockInterface::NAME => 'Default Stock',
                StockInterface::EXTENSION_ATTRIBUTES_KEY => [
                    'sales_channels' => []
                ]
            ],
            [
                StockInterface::STOCK_ID => 20,
                StockInterface::NAME => 'US-stock',
                StockInterface::EXTENSION_ATTRIBUTES_KEY => [
                    'sales_channels' => []
                ]
            ],
            [
                StockInterface::STOCK_ID => 30,
                StockInterface::NAME => 'Global-stock',
                StockInterface::EXTENSION_ATTRIBUTES_KEY => [
                    'sales_channels' => []
                ]
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockIdToDelete,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];
        (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['stockId' => $stockIdToDelete]);

        $actualData = $this->getStocksList();
        self::assertEquals(3, $actualData['total_count']);
        AssertArrayContains::assert($expectedStocksAfterDeleting, $actualData['items']);
    }

    /**
     * @return array
     */
    private function getStocksList(): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        return $this->_webApiCall($serviceInfo);
    }
}
