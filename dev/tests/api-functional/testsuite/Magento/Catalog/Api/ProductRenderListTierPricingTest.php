<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Test tier pricing functionality in products-render-info API
 */
class ProductRenderListTierPricingTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/products-render-info';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * Set up test dependencies
     */
    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple',
                'website_id' => 0,
                'tier_prices' => [
                    [
                        'customer_group_id' => 2,
                        'qty' => 1,
                        'value' => 6
                    ]
                ]
            ]
        ),
        DataFixture(CustomerFixture::class, ['group_id' => 2], as: 'customer_a'),
        DataFixture(CustomerFixture::class, ['group_id' => 1], as: 'customer_b')
    ]
    public function testGuestUserGetsBasePriceNotTierPrice(): void
    {
        $searchCriteria = [
            'searchCriteria' => [
                'page_size' => 1,
            ],
            'store_id' => 1,
            'currencyCode' => 'USD'
        ];
        $customerEmail = DataFixtureStorageManager::getStorage()->get('customer_a')->getEmail();
        $customerPassword = "password";
        $token = $this->customerTokenService
            ->createCustomerAccessToken($customerEmail, $customerPassword);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH .
                    '?' . http_build_query($searchCriteria) . '&storeId=1&currencyCode=USD',
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $token,
            ],
        ];
        $response = $this->_webApiCall($serviceInfo);
        $this->assertArrayHasKey('items', $response);
        $this->assertNotEmpty($response['items']);
        $product = reset($response['items']);
        $this->assertArrayHasKey('price_info', $product);
        $finalPrice = $product['price_info']['final_price'];
        $this->assertEquals(6, $finalPrice, 'customer_a should get tier price');

        $customerEmail = DataFixtureStorageManager::getStorage()->get('customer_b')->getEmail();
        $token = $this->customerTokenService
            ->createCustomerAccessToken($customerEmail, $customerPassword);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH .
                    '?' . http_build_query($searchCriteria) . '&storeId=1&currencyCode=USD',
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $token,
            ],
        ];

        $response = $this->_webApiCall($serviceInfo);
        $product = reset($response['items']);
        $this->assertArrayHasKey('price_info', $product);
        $finalPrice = $product['price_info']['final_price'];
        $this->assertEquals(10, $finalPrice, 'customer_b should not get tier price');
    }
}
