<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Catalog\Helper\Data;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
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
 * GraphQl tests for @see \Magento\SalesGraphQl\Model\Resolver\CustomerOrders.
 */
class CustomerOrdersTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test graphql customer orders
     *
     * @throws LocalizedException
     */
    #[
        Config(Data::XML_PATH_PRICE_SCOPE, Data::PRICE_SCOPE_WEBSITE),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store3'),
        DataFixture(ProductFixture::class, ['website_ids' => [1, '$website2.id$' ]], as: 'product'),
        DataFixture(
            Customer::class,
            [
                'store_id' => '$store2.id$',
                'website_id' => '$website2.id$',
                'addresses' => [[]]
            ],
            as: 'customer'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote1', scope: 'store2'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$quote1.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$quote1.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$quote1.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote1.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote1.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote1.id$'], 'order1'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote2', scope: 'store3'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$quote2.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$quote2.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$quote2.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote2.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote2.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote2.id$'], 'order2')
    ]
    public function testGetCustomerOrders()
    {
        $store2 = $this->fixtures->get('store2');
        $store3 = $this->fixtures->get('store3');
        $customer = $this->fixtures->get('customer');
        $currentEmail = $customer->getEmail();
        $currentPassword = 'password';

        $generateToken = $this->generateCustomerToken($currentEmail, $currentPassword);
        $tokenResponse = $this->graphQlMutationWithResponseHeaders(
            $generateToken,
            [],
            '',
            ['Store' => $store2->getCode()]
        );
        $customerToken = $tokenResponse['body']['generateCustomerToken']['token'];

        $query = $this->getCustomerOrdersQuery('STORE');
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerHeaders($customerToken, $store2->getCode())
        );

        $this->assertEquals(2, count($response['customer']['orders']['items']));

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerHeaders($customerToken, $store3->getCode())
        );

        $this->assertEquals(2, count($response['customer']['orders']['items']));

        $query = $this->getCustomerOrdersQuery('WEBSITE');
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerHeaders($customerToken, $store2->getCode())
        );

        $this->assertEquals(2, count($response['customer']['orders']['items']));

        $query = $this->getCustomerOrdersQuery('GLOBAL');
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerHeaders($customerToken, $store2->getCode())
        );

        $this->assertEquals(2, count($response['customer']['orders']['items']));

        $query = $this->getCustomerOrdersQuery();
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerHeaders($customerToken, $store2->getCode())
        );

        $this->assertEquals(1, count($response['customer']['orders']['items']));
    }

    /**
     * Test graphql customer orders when customer doesn't have access to custom website in Multi-Store setup.

     * @dataProvider dataProviderScope
     */
    #[
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store3'),
        DataFixture(ProductFixture::class, ['website_ids' => [1, '$website2.id$' ]], as: 'product'),
        DataFixture(
            Customer::class,
            [
                'store_id' => '$store2.id$',
                'website_id' => '$website2.id$',
                'addresses' => [[]]
            ],
            as: 'customer'
        )
    ]
    public function testGetCustomerOrdersCustomerHasNoAccess($scope)
    {
        $store2 = $this->fixtures->get('store2');
        $customer = $this->fixtures->get('customer');
        $currentEmail = $customer->getEmail();
        $currentPassword = 'password';

        $generateToken = $this->generateCustomerToken($currentEmail, $currentPassword);
        $tokenResponse = $this->graphQlMutationWithResponseHeaders(
            $generateToken,
            [],
            '',
            ['Store' => $store2->getCode()]
        );
        $customerToken = $tokenResponse['body']['generateCustomerToken']['token'];

        $query = $this->getCustomerOrdersQuery($scope);

        $this->expectException(\Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');
        $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerHeaders($customerToken, null)
        );
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
     * Generate graphql query body for customer orders
     *
     * @param string|null $scope
     *
     * @return array|string
     */
    private function getCustomerOrdersQuery(?string $scope = null): array|string
    {
        $query = <<<QUERY
query {
	customer {
		orders(
			pageSize: 20,
            {{scope}}
		) {
			items {
				id
				order_number
				order_date
				total {
				grand_total
				{ value currency }}
				status
			}
		}
	}
}
QUERY;
        $query = str_replace("{{scope}}", isset($scope) ? "scope: $scope" : '', $query);
        return $query;
    }

    /**
     * Get customer login token
     *
     * @param string $email
     * @param string $password
     * @return string
     */
    private function generateCustomerToken(string $email, string $password) : string
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

    /**
     * Scopes Data provider
     *
     * @return array
     */
    public function dataProviderScope()
    {
        return [
            'store scope' => ['STORE'],
            'website scope' => ['WEBSITE'],
            'global scope' => ['GLOBAL'],
            'no scope' => [null],
        ];
    }
}
