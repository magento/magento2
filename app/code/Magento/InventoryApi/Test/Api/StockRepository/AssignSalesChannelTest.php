<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\StockRepository;

use Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Assert\AssertArrayContains;

class AssignSalesChannelTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stocks';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    /**
     */
    public function testDefaultWebsiteToDefaultStock()
    {
        $stockId = 1;
        $expectedData = [
            StockInterface::STOCK_ID => $stockId,
            StockInterface::NAME => 'Default Stock',
            'extension_attributes' => [
                'sales_channels' => [
                    [
                        'type' => SalesChannelInterface::TYPE_WEBSITE,
                        'code' => 'base'
                    ]
                ]
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $this->_webApiCall($serviceInfo, ['stock' => $expectedData]);

        AssertArrayContains::assert($expectedData, $this->getStockDataById($stockId));
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     */
    public function testAssignAdditionalWebsitesToDefaultStock()
    {
        $stockId = 1;
        $expectedData = [
            StockInterface::STOCK_ID => $stockId,
            StockInterface::NAME => 'Default Stock',
            'extension_attributes' => [
                'sales_channels' => [
                    [
                        'type' => SalesChannelInterface::TYPE_WEBSITE,
                        'code' => 'eu_website'
                    ],
                    [
                        'type' => SalesChannelInterface::TYPE_WEBSITE,
                        'code' => 'us_website'
                    ]
                ]
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $this->_webApiCall($serviceInfo, ['stock' => $expectedData]);

        AssertArrayContains::assert($expectedData, $this->getStockDataById($stockId));
    }

    /**
     * @param int $stockId
     * @return array
     */
    private function getStockDataById(int $stockId): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['stockId' => $stockId]);
        self::assertArrayHasKey(StockInterface::STOCK_ID, $response);
        return $response;
    }
}
