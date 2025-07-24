<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Indexer\Test\Fixture\Indexer as IndexerFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test API error processor for malformed requests/bodies.
 */
class ApiErrorProcessorTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/';

    /**
     * @var DataFixtureStorage
     */
    protected $fixtures;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        parent::setUp();
        /** @var DataFixtureStorage $fixtures */
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Test that the API returns a 400 error when the request params are malformed.
     *
     * @param array $requestData
     * @param string $endpoint
     * @param int $expectedExceptionCode
     *
     * @dataProvider malformedRequestParamsDataProvider
     */
    public function testMalformedRequestParams(array $requestData, string $endpoint, int $expectedExceptionCode)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode($expectedExceptionCode);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $endpoint . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ]
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Data provider for testMalformedRequestParams
     *
     * @return array
     */
    public static function malformedRequestParamsDataProvider()
    {
        return [
            'empty_filter_groups_value' => [
                'requestData' => [
                    'searchCriteria' => [
                        'filterGroups' => [
                            [
                                'filters' => [
                                    [
                                        'field' => 'string'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'endpoint' => 'coupons/search',
                'expectedExceptionCode' => 400,
            ],
            'empty_filter_groups_value2' => [
                'requestData' => [
                    'searchCriteria' => [
                        'filterGroups' => [
                            [
                                'filters' => [
                                    [
                                        'field' => 'string'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'endpoint' => 'categories/attributes',
                'expectedExceptionCode' => 400,
            ],
            'empty_sort_orders' => [
                'requestData' => [
                    'searchCriteria' => [
                        'sortOrders' => [
                            [
                                'field' => 'string'
                            ]
                        ]
                    ]
                ],
                'endpoint' => 'cmsPage/search',
                'expectedExceptionCode' => 400,
            ]
        ];
    }

    /**
     * Test that the POST API returns a 400 error when the request body is malformed.
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(IndexerFixture::class, as: 'indexer'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
    ]
    public function testPOSTWithMalformedBody(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(400);

        $cart = $this->fixtures->get('cart');
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cart->getId(), 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'guest-carts/' . $cartId . '/shipping-information',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = [
            "addressInformation" => [
                "extension_attributes" => [
                    "discounts" => [
                        [
                            "discount_data" => [
                                "amount" => 0
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Test that the PUT API returns a 400 error when the request body is malformed.
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(IndexerFixture::class, as: 'indexer'),
    ]
    public function testPUTWithMalformedBody(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(400);

        $product = $this->fixtures->get('product');
        $sku = $product->getSku();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'products/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
        ];
        $requestData = [
            "product" => [
                "extension_attributes" => [
                    "stock_item" => [
                        "show_default_notification_message" => true
                    ]
                ]
            ]
        ];
        $this->_webApiCall($serviceInfo, $requestData);
    }
}
