<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\SourceRepository;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GetListTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/sources';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testGetList()
    {
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => SourceInterface::ENABLED,
                                'value' => 1,
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                SearchCriteria::SORT_ORDERS => [
                    [
                        'field' => SourceInterface::NAME,
                        'direction' => SortOrder::SORT_DESC,
                    ],
                ],
                SearchCriteria::CURRENT_PAGE => 2,
                SearchCriteria::PAGE_SIZE => 2,
            ],
        ];
        $expectedTotalCount = 4;
        $expectedItemsData = [
            [
                SourceInterface::ENABLED => true,
                SourceInterface::NAME => 'EU-source-2',
            ],
            [
                SourceInterface::ENABLED => true,
                SourceInterface::NAME => 'EU-source-1',
            ],
        ];

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
        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);

        AssertArrayContains::assert($requestData['searchCriteria'], $response['search_criteria']);
        self::assertGreaterThanOrEqual($expectedTotalCount, $response['total_count']);
        AssertArrayContains::assert($expectedItemsData, $response['items']);
    }
}
