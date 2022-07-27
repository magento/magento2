<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * GraphQl tests for @see \Magento\SalesGraphQl\Model\Resolver\CustomerOrders.
 */
class GetCustomerOrdersTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->customerAuthUpdate = $this->objectManager->get(CustomerAuthUpdate::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
    }

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
                'email' => 'customer_graphql@mail.com',
                'password' => 'password',
                'store_id' => '$store2.id$',
                'website_id' => '$website2.id$',
                'addresses' => [
                    [
                        'country_id' => 'US',
                        'region_id' => 32,
                        'city' => 'Boston',
                        'street' => ['10 Milk Street'],
                        'postcode' => '02108',
                        'telephone' => '1234567890',
                        'default_billing' => true,
                        'default_shipping' => true
                    ]
                ]
            ],
            as: 'customer'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$', 'store_id' => '$store2.id$'], as: 'quote'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testGetCustomerOrders()
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $store2 = $fixtures->get('store2');
        $customer = $fixtures->get('customer');
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

        $query = <<<QUERY
query {
	customer {
		orders(
			pageSize: 20,
            scope: STORE
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
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($customerToken)
        );

        $this->assertNull($response['customer']['id']);
        $this->assertEquals('John', $response['customer']['firstname']);
        $this->assertEquals('Smith', $response['customer']['lastname']);
        $this->assertEquals($currentEmail, $response['customer']['email']);
    }

    /**
     * @param string $token
     *
     * @return array
     */
    private function getCustomerAuthHeaders(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
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
}
