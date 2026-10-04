<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Catalog\Test\Fixture\Product;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Store\Test\Fixture\Website;
use Magento\Store\Test\Fixture\Store;
use Magento\Store\Test\Fixture\Group;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Web API REST test for ValidateProductWebsiteAssignment plugins using fixtures
 *
 * @magentoAppArea webapi_rest
 */
class ValidateProductWebsiteAssignmentTest extends WebapiAbstract
{
    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @var string
     */
    private $customerToken;

    /**
     * @var string
     */
    private $guestCartId;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Test customer cart REST API validates product website assignment successfully
     */
    #[
        DataFixture(Website::class, ['code' => 'test_website', 'name' => 'Test Website'], 'website2'),
        DataFixture(Group::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(Store::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(
            Product::class,
            [
                'sku' => 'product-base-website',
                'name' => 'Product Base Website',
                'price' => 10.00,
                'website_ids' => [1, '$website2.id$'], // Base website only
                'stock_data' => ['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1]
            ],
            'product_base'
        ),
        DataFixture(Customer::class, as: 'customer'),
    ]
    public function testCustomerCartRestApiValidatesProductWebsiteAssignmentSuccessfully(): void
    {
        $customer = $this->fixtures->get('customer');
        $product = $this->fixtures->get('product_base');

        // Get customer token
        $this->customerToken = $this->getCustomerToken($customer->getEmail(), 'password');

        // Create customer cart
        $this->createCustomerCart();

        // Add product to cart via REST API
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                'token' => $this->customerToken,
            ],
        ];

        $cartItem = [
            'cartItem' => [
                'sku' => $product->getSku(),
                'qty' => 1,
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, $cartItem);

        $this->assertNotNull($result);
        $this->assertEquals($product->getSku(), $result['sku']);
        $this->assertEquals(1, $result['qty']);
    }

    /**
     * Test customer cart REST API throws exception when product not on website
     */
    #[
        DataFixture(Website::class, ['code' => 'test_website', 'name' => 'Test Website'], 'website2'),
        DataFixture(Group::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(Store::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(
            Product::class,
            [
                'sku' => 'product-second-website',
                'name' => 'Product Second Website',
                'price' => 15.00,
                'website_ids' => ['$website2.id$'], // Second website only
                'stock_data' => ['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1]
            ],
            'product_second'
        ),
        DataFixture(
            Customer::class,
            [
                'website_id' => 1 // Base website
            ],
            'customer'
        )
    ]
    public function testCustomerCartRestApiThrowsExceptionWhenProductNotOnWebsite(): void
    {
        $customer = $this->fixtures->get('customer');
        $product = $this->fixtures->get('product_second');

        $this->customerToken = $this->getCustomerToken($customer->getEmail(), 'password');

        $this->createCustomerCart();

        // Try to add product from wrong website
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                'token' => $this->customerToken,
            ],
        ];

        $cartItem = [
            'cartItem' => [
                'sku' => $product->getSku(),
                'qty' => 1,
            ],
        ];

        // This should throw an exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product that you are trying to add is not available.');

        $this->_webApiCall($serviceInfo, $cartItem);
    }

    /**
     * Test guest cart REST API throws exception when product not on website
     */
    #[
        DataFixture(Website::class, ['code' => 'test_website', 'name' => 'Test Website'], 'website2'),
        DataFixture(Group::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(Store::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(
            Product::class,
            [
                'sku' => 'product-second-guest',
                'name' => 'Product Second Guest',
                'price' => 18.00,
                'website_ids' => ['$website2.id$'], // Second website only
                'stock_data' => ['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1]
            ],
            'product_second'
        )
    ]
    public function testGuestCartRestApiThrowsExceptionWhenProductNotOnWebsite(): void
    {
        $product = $this->fixtures->get('product_second');

        $this->setupGuestCart();

        // Try to add product from wrong website
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $this->guestCartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];

        $cartItem = [
            'cartItem' => [
                'sku' => $product->getSku(),
                'qty' => 1,
            ],
        ];

        // This should throw an exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product that you are trying to add is not available.');

        $this->_webApiCall($serviceInfo, $cartItem);
    }

    /**
     * Get customer authentication token
     */
    private function getCustomerToken(string $email, string $password): string
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/integration/customer/token',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];

        $credentials = [
            'username' => $email,
            'password' => $password,
        ];

        return $this->_webApiCall($serviceInfo, $credentials);
    }

    /**
     * Create customer cart
     */
    private function createCustomerCart(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                'token' => $this->customerToken,
            ],
        ];

        $this->_webApiCall($serviceInfo);
    }

    /**
     * Setup guest cart
     */
    private function setupGuestCart(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];

        $this->guestCartId = $this->_webApiCall($serviceInfo);
    }
}
