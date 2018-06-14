<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class SourceItemsDeleteTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/source-items';
    const RESOURCE_DELETE_PATH = '/V1/inventory/source-items-delete';
    const SERVICE_NAME = 'inventoryApiSourceItemsDeleteV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testExecute()
    {
        $sourceItemsForDelete = [
            [
                SourceItemInterface::SOURCE_CODE => 'eu-1',
                SourceItemInterface::SKU => 'SKU-1',
            ],
            [
                SourceItemInterface::SOURCE_CODE => 'eu-2',
                SourceItemInterface::SKU => 'SKU-1',
            ],
        ];
        $expectedSourceItemsAfterDeleting = [
            [
                SourceItemInterface::SOURCE_CODE => 'eu-3',
                SourceItemInterface::SKU => 'SKU-1',
                SourceItemInterface::QUANTITY => 10,
                SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
            ],
            [
                SourceItemInterface::SOURCE_CODE => 'eu-disabled',
                SourceItemInterface::SKU => 'SKU-1',
                SourceItemInterface::QUANTITY => 10,
                SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_DELETE_PATH . '?'
                    . http_build_query(['sourceItems' => $sourceItemsForDelete]),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];
        (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['sourceItems' => $sourceItemsForDelete]);

        $actualData = $this->getSourceItems();

        self::assertEquals(2, $actualData['total_count']);
        AssertArrayContains::assert($expectedSourceItemsAfterDeleting, $actualData['items']);
    }

    /**
     * @return array
     */
    private function getSourceItems(): array
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
            ],
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'inventoryApiSourceItemRepositoryV1',
                'operation' => 'inventoryApiSourceItemRepositoryV1GetList',
            ],
        ];
        return (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);
    }
}
