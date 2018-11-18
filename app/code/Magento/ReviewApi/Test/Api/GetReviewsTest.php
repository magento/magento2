<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewApi\Test\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Webapi\Rest\Request;
use Magento\ReviewApi\Api\Data\ReviewInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class GetReviewsTest
 */
class GetReviewsTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/get-reviews';
    const SERVICE_NAME = 'reviewApiGetReviewsV1';

    /**#@-*/
    /**
     * @magentoApiDataFixture Magento/Review/_files/different_reviews.php
     * @magentoApiDataFixture Magento/Review/_files/reviews.php
     */
    public function testGetProductReviews()
    {
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => ProductInterface::SKU,
                                'value' => 'simple3',
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                SearchCriteria::SORT_ORDERS => [
                    [
                        'field' => ReviewInterface::CREATED_AT,
                        'direction' => SortOrder::SORT_DESC,
                    ],
                ],
                SearchCriteria::CURRENT_PAGE => 1,
                SearchCriteria::PAGE_SIZE => 10,
            ],
        ];
        $expectedTotalCount = 1;
        $expectedItemsData = [
            [
                'related_entity_id' => 12,
                'customer_nickname' => 'Nickname',
                'title' => 'Review Summary',
                'review_text' => 'Review text',
            ],
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);
        AssertArrayContains::assert($requestData['searchCriteria'], $response['search_criteria']);
        self::assertEquals($expectedTotalCount, $response['total_count']);
        AssertArrayContains::assert($expectedItemsData, $response['items']);
    }

    /**#@-*/
    /**
     * @magentoApiDataFixture Magento/Review/_files/different_reviews.php
     * @magentoApiDataFixture Magento/Review/_files/customer_review.php
     */
    public function testGetCustomerReviews()
    {
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => ReviewInterface::CUSTOMER_ID,
                                'value' => 1,
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                SearchCriteria::SORT_ORDERS => [
                    [
                        'field' => ReviewInterface::CREATED_AT,
                        'direction' => SortOrder::SORT_DESC,
                    ],
                ],
                SearchCriteria::CURRENT_PAGE => 1,
                SearchCriteria::PAGE_SIZE => 10,
            ],
        ];
        $expectedTotalCount = 1;
        $expectedItemsData = [
            [
                'customer_id' => 1,
                'customer_nickname' => 'Nickname',
                'title' => 'Review Summary',
                'review_text' => 'Review text',
            ],
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
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
