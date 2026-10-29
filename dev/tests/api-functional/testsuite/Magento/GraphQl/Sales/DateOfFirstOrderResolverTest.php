<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * GraphQl tests for @see \Magento\SalesGraphQl\Model\Resolver\DateOfFirstOrderResolver
 */
#[
    Config(Data::XML_PATH_PRICE_SCOPE, Data::PRICE_SCOPE_WEBSITE),
    DataFixture(WebsiteFixture::class, as: 'website2'),
    DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
    DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
    DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store3'),
    DataFixture(ProductFixture::class, ['website_ids' => [1, '$website2.id$']], as: 'product'),
    DataFixture(Indexer::class, as: 'indexer'),
    DataFixture(
        CustomerFixture::class,
        [
            'store_id' => '$store2.id$',
            'website_id' => '$website2.id$',
            'addresses' => [[]]
        ],
        as: 'customer'
    ),
    DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote1', scope: 'store2'),
    DataFixture(AddProductToCartFixture::class, [
        'cart_id' => '$quote1.id$', 'product_id' => '$product.id$', 'qty' => 1
    ]),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote1.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote1.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote1.id$']),
    DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote1.id$']),
    DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote1.id$'], 'order1'),
    DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote2', scope: 'store3'),
    DataFixture(AddProductToCartFixture::class, [
        'cart_id' => '$quote2.id$', 'product_id' => '$product.id$', 'qty' => 1
    ]),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote2.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote2.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote2.id$']),
    DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote2.id$']),
    DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote2.id$'], 'order2')
]
class DateOfFirstOrderResolverTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test the date_of_first_order data for customerOrders query in Global scope
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function testGetCustomerOrdersGlobalScope()
    {
        $store2 = $this->fixtures->get('store2');
        $customerEmail = $this->fixtures->get('customer')->getEmail();
        $generateToken = $this->generateCustomerToken($customerEmail, 'password');
        $tokenResponse = $this->graphQlMutationWithResponseHeaders(
            $generateToken,
            [],
            '',
            ['Store' => $store2->getCode()]
        );
        $token = $tokenResponse['body']['generateCustomerToken']['token'];

        $customerAuthHeaders = $this->getCustomerHeaders($token, $store2->getCode());
        $query = $this->getCustomerOrdersQuery();
        $response = $this->graphQlQuery($query, [], '', $customerAuthHeaders);
        self::assertEquals(1, count($response['customer']['orders']['items']));
        self::assertArrayHasKey('date_of_first_order', $response['customer']['orders']);

        $customerAuthHeaders = $this->getCustomerHeaders($token, $store2->getCode());
        $query = $this->getCustomerOrdersQueryWithFilters('GLOBAL');
        $response = $this->graphQlQuery($query, [], '', $customerAuthHeaders);
        self::assertNotEmpty($response['customer']['orders']['items']);
        self::assertEquals(2, count($response['customer']['orders']['items']));
        self::assertArrayHasKey('date_of_first_order', $response['customer']['orders']);

        $customerAuthHeaders = $this->getCustomerHeaders($token, null);
        $query = $this->getCustomerOrdersQueryWithFilters('GLOBAL');
        $this->expectException(\Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');
        $this->graphQlQuery($query, [], '', $customerAuthHeaders);
    }

    /**
     * Test the date_of_first_order data for customerOrders query in Website scope
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function testGetCustomerOrdersWebsiteScope()
    {
        $store2 = $this->fixtures->get('store2');
        $customerEmail = $this->fixtures->get('customer')->getEmail();
        $generateToken = $this->generateCustomerToken($customerEmail, 'password');
        $tokenResponse = $this->graphQlMutationWithResponseHeaders(
            $generateToken,
            [],
            '',
            ['Store' => $store2->getCode()]
        );
        $token = $tokenResponse['body']['generateCustomerToken']['token'];
        $customerAuthHeaders = $this->getCustomerHeaders($token, $store2->getCode());

        $query = $this->getCustomerOrdersQueryWithFilters('WEBSITE');
        $response = $this->graphQlQuery($query, [], '', $customerAuthHeaders);
        self::assertEquals(2, count($response['customer']['orders']['items']));
        self::assertArrayHasKey('date_of_first_order', $response['customer']['orders']);
    }

    /**
     * Test the date_of_first_order data for customerOrders query in Store scope
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function testGetCustomerOrdersStoreScope()
    {
        $store2 = $this->fixtures->get('store2');
        $store3 = $this->fixtures->get('store3');
        $customerEmail = $this->fixtures->get('customer')->getEmail();
        $generateToken = $this->generateCustomerToken($customerEmail, 'password');
        $tokenResponse = $this->graphQlMutationWithResponseHeaders(
            $generateToken,
            [],
            '',
            ['Store' => $store2->getCode()]
        );
        $token = $tokenResponse['body']['generateCustomerToken']['token'];

        $customerAuthHeaders = $this->getCustomerHeaders($token, $store2->getCode());
        $query = $this->getCustomerOrdersQueryWithFilters('STORE', null);
        $response = $this->graphQlQuery($query, [], '', $customerAuthHeaders);
        self::assertEquals(2, count($response['customer']['orders']['items']));
        self::assertArrayHasKey('date_of_first_order', $response['customer']['orders']);

        $customerAuthHeaders = $this->getCustomerHeaders($token, $store3->getCode());
        $response = $this->graphQlQuery($query, [], '', $customerAuthHeaders);
        self::assertEquals(2, count($response['customer']['orders']['items']));
        self::assertArrayHasKey('date_of_first_order', $response['customer']['orders']);

        $customerAuthHeaders = $this->getCustomerHeaders($token, null);
        $query = $this->getCustomerOrdersQueryWithFilters('STORE', '+1 years');
        $this->expectException(\Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');
        $this->graphQlQuery($query, [], '', $customerAuthHeaders);
    }

    /**
     * Test the date_of_first_order data for customerOrders query without any scope
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function testGetCustomerOrdersWithoutScope()
    {
        $store2 = $this->fixtures->get('store2');
        $customerEmail = $this->fixtures->get('customer')->getEmail();
        $generateToken = $this->generateCustomerToken($customerEmail, 'password');
        $tokenResponse = $this->graphQlMutationWithResponseHeaders(
            $generateToken,
            [],
            '',
            ['Store' => $store2->getCode()]
        );
        $token = $tokenResponse['body']['generateCustomerToken']['token'];
        $customerAuthHeaders = $this->getCustomerHeaders($token, $store2->getCode());

        $query = $this->getCustomerOrdersQueryWithFilters();
        $response = $this->graphQlQuery($query, [], '', $customerAuthHeaders);
        self::assertArrayHasKey('date_of_first_order', $response['customer']['orders']);
        self::assertNotNull($response['customer']['orders']['date_of_first_order']);
    }

    /**
     * Test the date_of_first_order data for customerOrders query without
     * any scope and store header
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1
        ]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testGetCustomerOrdersWithoutScopeAndStoreHeader()
    {
        $customerEmail = $this->fixtures->get('customer')->getEmail();
        $generateToken = $this->generateCustomerToken($customerEmail, 'password');
        $tokenResponse = $this->graphQlMutationWithResponseHeaders(
            $generateToken
        );
        $token = $tokenResponse['body']['generateCustomerToken']['token'];
        $customerAuthHeaders = $this->getCustomerHeaders($token, null);

        $query = $this->getCustomerOrdersQueryWithFilters();
        $response = $this->graphQlQuery($query, [], '', $customerAuthHeaders);
        self::assertArrayHasKey('date_of_first_order', $response['customer']['orders']);
        self::assertNotNull($response['customer']['orders']['date_of_first_order']);
    }

    /**
     * Get Customer Orders query
     *
     * @return string
     */
    private function getCustomerOrdersQuery(): string
    {
        return <<<QUERY
query {
    customer {
        orders(pageSize: 10) {
            total_count
            date_of_first_order
            items {
                number
                order_date
            }
        }
    }
}
QUERY;
    }

    /**
     * Get Customer Orders query with order_date and scope filter
     *
     * @param string|null $scope
     * @param string|null $duration
     * @return string
     */
    private function getCustomerOrdersQueryWithFilters(?string $scope = null, ?string $duration = null): string
    {
        $query = <<<QUERY
query {
    customer {
        orders(
        filter: {
            order_date: {
                from: "{{date}}"
            }
        },
        {{scope}}
        ) {
            total_count
            date_of_first_order
            items {
                number
                order_date
            }
        }
    }
}
QUERY;

        $query = str_replace("{{scope}}", isset($scope) ? "scope: $scope" : '', $query);

        return str_replace(
            "{{date}}",
            isset($duration) ? date('Y-m-d', strtotime($duration)) : date('Y-m-d'),
            $query
        );
    }

    /**
     * Test Customer Orders query without authorization
     *
     * @return void
     * @throws Exception
     */
    public function testCustomerOrdersQueryNotAuthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');
        $this->graphQlQuery($this->getCustomerOrdersQuery(), [], '', []);
    }

    /**
     * Generate graphql query headers for customer orders
     *
     * @param string $token
     * @param string|null $storeCode
     *
     * @return array
     */
    private function getCustomerHeaders(string $token, ?string $storeCode): array
    {
        return ['Authorization' => 'Bearer ' . $token, 'Store' => $storeCode ?? 'default'];
    }

    /**
     * Get customer login token
     *
     * @param string $email
     * @param string $password
     * @return string
     */
    private function generateCustomerToken(string $email, string $password): string
    {
        return <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$email}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;
    }
}
