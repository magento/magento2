<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\StockRepository;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GetListTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stocks';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @param array $searchCriteria
     * @param int $expectedTotalCount
     * @param array $expectedItemsData
     * @dataProvider dataProviderGetList
     */
    public function testGetList(array $searchCriteria, int $expectedTotalCount, array $expectedItemsData)
    {
        $requestData = ['searchCriteria' => $searchCriteria];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);

        AssertArrayContains::assert($searchCriteria, $response['search_criteria']);
        self::assertGreaterThanOrEqual($expectedTotalCount, $response['total_count']);
        AssertArrayContains::assert($expectedItemsData, $response['items']);
    }

    /**
     * @return array
     */
    public function dataProviderGetList(): array
    {
        return [
            'filtering' => [
                [
                    SearchCriteria::FILTER_GROUPS => [
                        [
                            'filters' => [
                                [
                                    'field' => StockInterface::NAME,
                                    'value' => 'EU-stock',
                                    'condition_type' => 'eq',
                                ],
                            ],
                        ],
                    ],
                ],
                1,
                [
                    [
                        StockInterface::NAME => 'EU-stock',
                    ],
                ],
            ],
            'ordering_paging' => [
                [
                    SearchCriteria::FILTER_GROUPS => [], // It is need for soap mode
                    SearchCriteria::SORT_ORDERS => [
                        [
                            'field' => StockInterface::NAME,
                            'direction' => SortOrder::SORT_DESC,
                        ],
                    ],
                    SearchCriteria::CURRENT_PAGE => 2,
                    SearchCriteria::PAGE_SIZE => 2,
                ],
                3,
                [
                    [
                        StockInterface::NAME => 'EU-stock',
                    ],
                ],
            ],
        ];
    }
}
