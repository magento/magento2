<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Test\Api\StockRepository;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class UpdateTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stock';
    const SERVICE_NAME = 'inventoryStockRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock/stock.php
     */
    public function testUpdate()
    {
        $stock = $this->getStockDataByName('stock-name-1');
        $stockId = $stock[StockInterface::STOCK_ID];
        $data = [
            StockInterface::NAME => 'stock-name-1-updated',
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
        $this->_webApiCall($serviceInfo, ['stock' => $data], null, 'all');

        AssertArrayContains::assert($data, $this->getStockDataById($stockId));
    }

    /**
     * @param string $name
     * @return array
     */
    private function getStockDataByName($name)
    {
        $searchCriteria = [
            'filter_groups' => [
                [
                    'filters' => [
                        [
                            'field' => StockInterface::NAME,
                            'value' => $name,
                            'condition_type' => 'eq',
                        ],
                    ],
                ],
                'page_size' => 1,
            ],
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query(['searchCriteria' => $searchCriteria]),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo);
        self::assertArrayHasKey('items', $response);
        return reset($response['items']);
    }

    /**
     * @param int $stockId
     * @return array
     */
    private function getStockDataById($stockId)
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
        $response = $this->_webApiCall($serviceInfo);
        self::assertArrayHasKey(StockInterface::STOCK_ID, $response);
        return $response;
    }
}
