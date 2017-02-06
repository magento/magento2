<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\TestFramework\TestCase\WebapiAbstract;

class SearchTest extends WebapiAbstract
{
    const SERVICE_NAME = 'searchV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/search';

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @covers \Magento\Framework\Search\Search::search
     */
    public function testCatalogSearch()
    {
        $searchCriteria = [
            'searchCriteria' => [
                'request_name' => 'quick_search_container',
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'search_term',
                                'value' => 'simple',
                                'condition_type' => 'eq'
                            ],
                            [
                                'field' => 'price_dynamic_algorithm',
                                'value' => 'auto',
                                'condition_type' => 'eq'
                            ]
                        ]
                    ]
                ],
                'page_size' => 20000000000000,
                'current_page' => 1,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Search',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertTrue($response['total_count'] > 0);
        $this->assertTrue(count($response['items']) > 0);

        $this->assertNotNull($response['items'][0]['id']);
        $this->assertEquals('score', $response['items'][0]['custom_attributes'][0]['attribute_code']);
        $this->assertTrue($response['items'][0]['custom_attributes'][0]['value'] > 0);
    }
}
