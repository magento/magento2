<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Exception;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

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

    /** @var  array */
    private $headers;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
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
        $this->assertArrayHasKey('cart_id', $response['customerCart']);
        $this->assertEquals($maskedQuoteId, $response['customerCart']['cart_id']);
        $this->assertEquals(
            $quantity,
            $response['customerCart']['items'][0]['quantity'],
            'Incorrect quantity of products in cart'
        );
    }

    /**
     * Query for customer cart for a user with no existing active cart
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetNewCustomerCart()
    {
        $customerToken = $this->generateCustomerToken();
        $customerCartQuery = $this->getCustomerCartQuery();
        $this->headers = ['Authorization' => 'Bearer ' . $customerToken];
        $response = $this->graphQlQuery($customerCartQuery, [], '', $this->headers);
        $this->assertArrayHasKey('customerCart', $response);
        $this->assertArrayHasKey('cart_id', $response['customerCart']);
        $this->assertNotNull($response['customerCart']['cart_id']);
        $this->assertEmpty($response['customerCart']['items']);
        $this->assertEquals(0, $response['customerCart']['total_quantity']);
    }

    /**
     * Query for customer cart with no customer token passed
     *
     * @expectedException Exception
     * @expectedExceptionMessage User cannot access the cart unless loggedIn and with a valid customer token
     */
    public function testGetCustomerCartWithNoCustomerToken()
    {
        $customerCartQuery = $this->getCustomerCartQuery();
        $this->graphQlQuery($customerCartQuery);
    }

    /**
     * Query for customer cart after customer token is revoked
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerCartAfterTokenRevoked()
    {
        $customerToken = $this->generateCustomerToken();
        $this->headers = ['Authorization' => 'Bearer ' . $customerToken];
        $customerCartQuery = $this->getCustomerCartQuery();
        $response = $this->graphQlMutation($customerCartQuery, [], '', $this->headers);
        $this->assertArrayHasKey('customerCart', $response);
        $this->assertArrayHasKey('cart_id', $response['customerCart']);
        $this->assertNotNull($response['customerCart']['cart_id']);
        $this->revokeCustomerToken();
        $customerCartQuery = $this->getCustomerCartQuery();
        $this->expectExceptionMessage(
            "User cannot access the cart unless loggedIn and with a valid customer token"
        );
        $this->graphQlQuery($customerCartQuery, [], '', $this->headers);
    }

    /**
     * Querying for the customer cart twice->should return the same cart
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRequestCustomerCartTwice()
    {
        $customerToken = $this->generateCustomerToken();
        $this->headers = ['Authorization' => 'Bearer ' . $customerToken];
        $customerCartQuery = $this->getCustomerCartQuery();
        $response = $this->graphQlMutation($customerCartQuery, [], '', $this->headers);
        $this->assertArrayHasKey('customerCart', $response);
        $this->assertArrayHasKey('cart_id', $response['customerCart']);
        $this->assertNotNull($response['customerCart']['cart_id']);
        $cartId = $response['customerCart']['cart_id'];
        $customerCartQuery = $this->getCustomerCartQuery();
        $response2 = $this->graphQlQuery($customerCartQuery, [], '', $this->headers);
        $this->assertEquals($cartId, $response2['customerCart']['cart_id']);
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
        $this->assertNotEmpty($response['customerCart']['cart_id']);
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
        $maskedQuoteIdSecondStore = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1_not_default_store');
        $customerCartQuery = $this->getCustomerCartQuery();

        $headerMap = $this->getHeaderMap();
        $headerMap['Store'] = 'fixture_second_store';
        $responseSecondStore = $this->graphQlQuery($customerCartQuery, [], '', $headerMap);
        $this->assertEquals($maskedQuoteIdSecondStore, $responseSecondStore['customerCart']['cart_id']);
    }

    /**
     * @return string
     */
    private function generateCustomerToken(): string
    {
        $query = <<<QUERY
mutation {
  generateCustomerToken(
    email: "customer@example.com"
    password: "password"
  ) {
    token
  }
}
QUERY;
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('generateCustomerToken', $response);
        self::assertArrayHasKey('token', $response['generateCustomerToken']);
        self::assertNotEmpty($response['generateCustomerToken']['token']);

        return $response['generateCustomerToken']['token'];
    }

    private function revokeCustomerToken()
    {
        $query = <<<QUERY
mutation{
  revokeCustomerToken{
    result
  }
}
QUERY;

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $this->assertTrue($response['revokeCustomerToken']['result']);
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCustomerCartQuery(): string
    {
        return <<<QUERY
{
  customerCart {
  total_quantity
  cart_id
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
}
