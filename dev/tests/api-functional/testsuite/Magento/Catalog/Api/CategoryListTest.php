<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

class CategoryListTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/categories/list';
    const SERVICE_NAME = 'catalogCategoryListV1';

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_tree.php
     */
    public function testGetList()
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'name',
                                'value' => 'Category 1',
                                'condition_type' => 'eq',
                            ],
                            [
                                'field' => 'name',
                                'value' => 'Category 1.1',
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                    [
                        'filters' => [
                            [
                                'field' => 'level',
                                'value' => 2,
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 2,
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
        $this->assertTrue($response['total_count'] > 0);
        $this->assertTrue(count($response['items']) > 0);

        $this->assertNotNull($response['items'][0]['name']);
        $this->assertEquals('Category 1', $response['items'][0]['name']);
    }
}
