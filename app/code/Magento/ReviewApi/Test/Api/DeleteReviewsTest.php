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
 * Class DeleteReviewsTest
 */
class DeleteReviewsTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const DELETE_RESOURCE_PATH = '/V1/delete-reviews';
    const DELETE_SERVICE_NAME = 'reviewApiDeleteReviewsV1';
    const GET_RESOURCE_PATH = '/V1/get-reviews';
    const GET_SERVICE_NAME = 'reviewApiGetReviewsV1';

    /**#@-*/
    /**
     * @magentoApiDataFixture Magento/Review/_files/different_reviews.php
     */
    public function testDeleteProductReviews()
    {
        $reviewsForDelete = [
            [
                'review_id' => 1,
                'related_entity_id' => 1,
                'review_entity_id' => 1, //product
                'customer_nickname' => 'Nickname',
                'title' => 'Review Summary',
                'review_text' => 'Review text',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ],
            [
                'review_id' => 2,
                'related_entity_id' => 1,
                'review_entity_id' => 1, //product
                'customer_nickname' => 'Nickname',
                'title' => '2 filter first review',
                'review_text' => 'Review text',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ],
        ];
        $expectedReviewsAfterDelete = [
            [
                'review_id' => 3,
                'related_entity_id' => 1,
                'review_entity_id' => 1, //product
                'customer_nickname' => 'Nickname',
                'title' => '1 filter second review',
                'review_text' => 'Review text',
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::DELETE_RESOURCE_PATH . '?' . http_build_query(['reviews' => $reviewsForDelete]),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::DELETE_SERVICE_NAME,
                'operation' => self::DELETE_SERVICE_NAME . 'Execute',
            ],
        ];
        (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['reviews' => $reviewsForDelete]);

        $actualData = $this->getReviews();

        self::assertEquals(1, $actualData['total_count']);
        AssertArrayContains::assert($expectedReviewsAfterDelete, $actualData['items']);
    }

    /**
     * @return array
     */
    private function getReviews()
    {
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => ProductInterface::SKU,
                                'value' => 'simple',
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
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::GET_RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::GET_SERVICE_NAME,
                'operation' => self::GET_SERVICE_NAME . 'Execute',
            ],
        ];

        return (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);
    }
}
