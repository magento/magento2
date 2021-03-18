<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Bulk\OperationInterface;

class OperationRepositoryInterfaceTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/bulk';
    const SERVICE_NAME = 'asynchronousOperationsOperationRepositoryV1';

    /**
     * @magentoApiDataFixture Magento/AsynchronousOperations/_files/operation_searchable.php
     */
    public function testGetListByBulkStartTime()
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'start_time',
                                'value' => '2010-10-10 00:00:00',
                                'condition_type' => 'lteq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertEquals(6, $response['total_count']);
        $this->assertCount(6, $response['items']);

        foreach ($response['items'] as $item) {
            $this->assertEquals('bulk-uuid-searchable-6', $item['bulk_uuid']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/AsynchronousOperations/_files/operation_searchable.php
     */
    public function testGetList()
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'bulk_uuid',
                                'value' => 'bulk-uuid-searchable-6',
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                    [
                        'filters' => [
                            [
                                'field' => 'status',
                                'value' => OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertEquals(1, $response['total_count']);
        $this->assertCount(1, $response['items']);

        foreach ($response['items'] as $item) {
            $this->assertEquals('bulk-uuid-searchable-6', $item['bulk_uuid']);
        }
    }
}
