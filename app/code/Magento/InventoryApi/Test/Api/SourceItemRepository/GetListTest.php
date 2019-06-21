<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\SourceItemRepository;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GetListTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/source-items';
    const SERVICE_NAME = 'inventoryApiSourceItemRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/529092/scenarios/1823821
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/529092/scenarios/1840662
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530616/scenarios/1824142
     */
    public function testGetList()
    {
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => 'SKU-1',
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                SearchCriteria::SORT_ORDERS => [
                    [
                        'field' => SourceItemInterface::QUANTITY,
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
                SourceItemInterface::SOURCE_CODE => 'eu-1',
                SourceItemInterface::SKU => 'SKU-1',
                SourceItemInterface::QUANTITY => 5.5,
                SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
            ],
            [
                SourceItemInterface::SOURCE_CODE => 'eu-2',
                SourceItemInterface::SKU => 'SKU-1',
                SourceItemInterface::QUANTITY => 3,
                SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
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
        $response = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);

        AssertArrayContains::assert($requestData['searchCriteria'], $response['search_criteria']);
        self::assertEquals($expectedTotalCount, $response['total_count']);
        AssertArrayContains::assert($expectedItemsData, $response['items']);
    }
}
