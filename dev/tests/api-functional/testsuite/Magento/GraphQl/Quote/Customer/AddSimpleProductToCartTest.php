<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test adding simple product to Cart
 */
class AddSimpleProductToCartTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     */
    public function testAddSimpleProductToCart()
    {
        $sku = 'simple_product';
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $qty);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response['addSimpleProductsToCart']);
        self::assertEquals($qty, $response['addSimpleProductsToCart']['cart']['items'][0]['qty']);
        self::assertEquals($sku, $response['addSimpleProductsToCart']['cart']['items'][0]['product']['sku']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testAddProductToNonExistentCart()
    {
        $sku = 'simple_product';
        $qty = 2;
        $maskedQuoteId = 'non_existent_masked_id';

        $query = $this->getQuery($maskedQuoteId, $sku, $qty);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a product with SKU "simple_product"
     */
    public function testNonExistentProductToCart()
    {
        $sku = 'simple_product';
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $sku, $qty);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testAddSimpleProductToGuestCart()
    {
        $sku = 'simple_product';
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $qty);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     */
    public function testAddSimpleProductToAnotherCustomerCart()
    {
        $sku = 'simple_product';
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $qty);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customer2@search.example.com'));
    }

    /**
     * @param string $maskedQuoteId
     * @param string $sku
     * @param int $qty
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $sku, int $qty): string
    {
        return <<<QUERY
mutation {
  addSimpleProductsToCart(input: {
    cart_id: "{$maskedQuoteId}", 
    cartItems: [
      {
        data: {
          qty: {$qty}
          sku: "{$sku}"
        }
      }                
    ]
  }) {
    cart {
      items {
        id
        qty
        product {
          sku
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Retrieve customer authorization headers
     *
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
