<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting Customer cart information
 */
class GetCustomerCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage;
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quoteCollection = $this->objectManager->create(Collection::class);
        foreach ($quoteCollection as $quote) {
            $quote->delete();
        }
        parent::tearDown();
    }

    /**
     * Query for an existing active customer cart
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testGetActiveCustomerCart()
    {
        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $customerCartQuery = $this->getCustomerCartQuery();
        $response = $this->graphQlQuery($customerCartQuery, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('customerCart', $response);
        $this->assertArrayHasKey('items', $response['customerCart']);
        $this->assertNotEmpty($response['customerCart']['items']);
        $this->assertEquals(2, $response['customerCart']['total_quantity']);
        $this->assertArrayHasKey('id', $response['customerCart']);
        $this->assertNotEmpty($response['customerCart']['id']);
        $this->assertEquals($maskedQuoteId, $response['customerCart']['id']);
        $this->assertEquals(
            $quantity,
            $response['customerCart']['items'][0]['quantity'],
            'Incorrect quantity of products in cart'
        );
    }

    /**
     * Query for an existing customer cart with no masked quote id
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart_without_masked_quote_id.php
     */
    public function testGetLoggedInCustomerCartWithoutMaskedQuoteId()
    {
        $customerCartQuery = $this->getCustomerCartQuery();
        $response = $this->graphQlQuery($customerCartQuery, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('customerCart', $response);
        $this->assertArrayHasKey('items', $response['customerCart']);
        $this->assertEmpty($response['customerCart']['items']);
        $this->assertEquals(0, $response['customerCart']['total_quantity']);
        $this->assertArrayHasKey('id', $response['customerCart']);
        $this->assertNotEmpty($response['customerCart']['id']);
        $this->assertNotNull($response['customerCart']['id']);
    }

    /**
     * Query for customer cart for a user with no existing active cart
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetNewCustomerCart()
    {
        $customerCartQuery = $this->getCustomerCartQuery();
        $response = $this->graphQlQuery($customerCartQuery, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('customerCart', $response);
        $this->assertArrayHasKey('id', $response['customerCart']);
        $this->assertNotNull($response['customerCart']['id']);
        $this->assertNotEmpty($response['customerCart']['id']);
        $this->assertEmpty($response['customerCart']['items']);
        $this->assertEquals(0, $response['customerCart']['total_quantity']);
    }

    /**
     * Query for customer cart with no customer token passed
     *
     */
    public function testGetCustomerCartWithNoCustomerToken()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The request is allowed for logged in customer');

        $customerCartQuery = $this->getCustomerCartQuery();
        $this->graphQlQuery($customerCartQuery);
    }

    /**
     * Test graphql customer cart should expect an exception when customer doesn't belong to given website
     */
    #[
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
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
    public function testGetCustomerCartCustomerNotBelongingToWebsite()
    {
        $this->expectException(\Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('The request is allowed for logged in customer');

        $customer = $this->fixtures->get('customer');
        $customStore = $this->fixtures->get('store2');

        $generateTokenQuery = $this->generateCustomerToken($customer->getEmail(), 'password');

        $tokenResponse = $this->graphQlMutation($generateTokenQuery, [], '', ['Store' => $customStore->getCode()]);
        $token = $tokenResponse['generateCustomerToken']['token'];

        $customerCartQuery = $this->getCustomerCartQuery();
        $this->graphQlMutation($customerCartQuery, [], '', ['Authorization' => 'Bearer ' . $token]);
    }

    /**
     * Query for customer cart after customer token is revoked
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerCartAfterTokenRevoked()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User token has been revoked');

        $customerCartQuery = $this->getCustomerCartQuery();
        $headers = $this->getHeaderMap();
        $response = $this->graphQlMutation($customerCartQuery, [], '', $headers);
        $this->assertArrayHasKey('customerCart', $response);
        $this->assertArrayHasKey('id', $response['customerCart']);
        $this->assertNotNull($response['customerCart']['id']);
        $this->assertNotEmpty($response['customerCart']['id']);
        $this->revokeCustomerToken();
        $customerCartQuery = $this->getCustomerCartQuery();
        $this->graphQlQuery($customerCartQuery, [], '', $headers);
    }

    /**
     * Querying for the customer cart twice->should return the same cart
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRequestCustomerCartTwice()
    {
        $customerCartQuery = $this->getCustomerCartQuery();
        $response = $this->graphQlMutation($customerCartQuery, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('customerCart', $response);
        $this->assertArrayHasKey('id', $response['customerCart']);
        $this->assertNotNull($response['customerCart']['id']);
        $cartId = $response['customerCart']['id'];
        $customerCartQuery = $this->getCustomerCartQuery();
        $response2 = $this->graphQlQuery($customerCartQuery, [], '', $this->getHeaderMap());
        $this->assertEquals($cartId, $response2['customerCart']['id']);
    }

    /**
     *  Query for inactive Customer cart - in case of not finding an active cart, it should create a new one
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/make_cart_inactive.php
     */
    public function testGetInactiveCustomerCart()
    {
        $customerCartQuery = $this->getCustomerCartQuery();
        $response = $this->graphQlQuery($customerCartQuery, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('customerCart', $response);
        $this->assertNotEmpty($response['customerCart']['id']);
        $this->assertEmpty($response['customerCart']['items']);
        $this->assertEmpty($response['customerCart']['total_quantity']);
    }

    /**
     * Querying for an existing customer cart for second store
     *
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote_customer_not_default_store.php
     */
    public function testGetCustomerCartSecondStore()
    {
        $customerCartQuery = $this->getCustomerCartQuery();
        $maskedQuoteIdSecondStore = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1_not_default_store');
        $headerMap = $this->getHeaderMap();
        $headerMap['Store'] = 'fixture_second_store';
        $responseSecondStore = $this->graphQlQuery($customerCartQuery, [], '', $headerMap);
        $this->assertEquals($maskedQuoteIdSecondStore, $responseSecondStore['customerCart']['id']);
    }

    /**
     * Query to revoke customer token
     *
     * @return void
     */
    private function revokeCustomerToken(): void
    {
        $query = <<<QUERY
mutation{
  revokeCustomerToken{
    result
  }
}
QUERY;

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Query customer cart
     *
     * @return string
     */
    private function getCustomerCartQuery(): string
    {
        return <<<QUERY
{
  customerCart {
    total_quantity
    id
    items {
      id
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }

    /**
     * Create a header with customer token
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }

    /**
     * Get customer login token query
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
}
