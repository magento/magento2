<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Exception;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting cart information
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
     * @var CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    private $headers;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->customerRegistry = $objectManager->get(CustomerRegistry::class);
        $this->customerAuthUpdate = $objectManager->get(CustomerAuthUpdate::class);
        /** @var CartManagementInterface $cartManagement */
        $this->cartManagement = $objectManager->get(CartManagementInterface::class);
        /** @var CartRepositoryInterface $cartRepository */
        $this->cartRepository = $objectManager->get(CartRepositoryInterface::class);
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
            2,
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
        //$maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $customerToken = $this->generateCustomerToken();
        $customerCartQuery = $this->getCustomerCartQuery();
        $this->headers = ['Authorization' => 'Bearer ' . $customerToken];
        $response = $this->graphQlQuery($customerCartQuery, [], '', $this->headers);
        $i = 0;
        $this->assertArrayHasKey('customerCart', $response);
        $this->assertArrayHasKey('cart_id', $response['customerCart']);
        $this->assertNotNull($response['customerCart']['cart_id']);
    }

    /**
     * Query for customer cart with no customer token passed
     */
    public function testGetCustomerCartWithNoCustomerToken()
    {
        $customerCartQuery = $this->getCustomerCartQuery();
        $this->graphQlQuery($customerCartQuery);
        $i = 0;
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
        $maskedQuoteId =  $response['customerCart']['cart_id'];

        $this->revokeCustomerToken();
        $this->getCustomerCartQuery();
    }

    /**
     * Querying for the customer cart twice->should return the same cart
     */
    public function testRequestCustomerCartTwice()
    {

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
