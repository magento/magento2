<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Exception;
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

    protected function setUp(): void
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
        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response['addSimpleProductsToCart']);
        self::assertArrayHasKey('shipping_addresses', $response['addSimpleProductsToCart']['cart']);
        self::assertEmpty($response['addSimpleProductsToCart']['cart']['shipping_addresses']);
        self::assertEquals($quantity, $response['addSimpleProductsToCart']['cart']['items'][0]['quantity']);
        self::assertEquals($sku, $response['addSimpleProductsToCart']['cart']['items'][0]['product']['sku']);
        self::assertArrayHasKey('prices', $response['addSimpleProductsToCart']['cart']['items'][0]);

        self::assertArrayHasKey('price', $response['addSimpleProductsToCart']['cart']['items'][0]['prices']);
        $price = $response['addSimpleProductsToCart']['cart']['items'][0]['prices']['price'];
        self::assertArrayHasKey('value', $price);
        self::assertEquals(10, $price['value']);
        self::assertArrayHasKey('currency', $price);
        self::assertEquals('USD', $price['currency']);

        self::assertArrayHasKey('row_total', $response['addSimpleProductsToCart']['cart']['items'][0]['prices']);
        $rowTotal = $response['addSimpleProductsToCart']['cart']['items'][0]['prices']['row_total'];
        self::assertArrayHasKey('value', $rowTotal);
        self::assertEquals(20, $rowTotal['value']);
        self::assertArrayHasKey('currency', $rowTotal);
        self::assertEquals('USD', $rowTotal['currency']);

        self::assertArrayHasKey(
            'row_total_including_tax',
            $response['addSimpleProductsToCart']['cart']['items'][0]['prices']
        );
        $rowTotalIncludingTax =
            $response['addSimpleProductsToCart']['cart']['items'][0]['prices']['row_total_including_tax'];
        self::assertArrayHasKey('value', $rowTotalIncludingTax);
        self::assertEquals(20, $rowTotalIncludingTax['value']);
        self::assertArrayHasKey('currency', $rowTotalIncludingTax);
        self::assertEquals('USD', $rowTotalIncludingTax['currency']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException Exception
     * @expectedExceptionMessage Required parameter "cart_id" is missing
     */
    public function testAddSimpleProductToCartIfCartIdIsEmpty()
    {
        $query = <<<QUERY
mutation {
  addSimpleProductsToCart(
    input: {
      cart_id: "",
      cart_items: []
    }
  ) {
    cart {
      items {
        id
      }
    }
  }
}
QUERY;

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException Exception
     * @expectedExceptionMessage Required parameter "cart_items" is missing
     */
    public function testAddSimpleProductToCartIfCartItemsAreEmpty()
    {
        $query = <<<QUERY
mutation {
  addSimpleProductsToCart(
    input: {
      cart_id: "cart_id",
      cart_items: []
    }
  ) {
    cart {
      items {
        id
      }
    }
  }
}
QUERY;

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     *
     * @expectedException Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testAddProductToNonExistentCart()
    {
        $sku = 'simple_product';
        $quantity = 2;
        $maskedQuoteId = 'non_existent_masked_id';

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     *
     * @expectedException Exception
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
     * @param float $quantity
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $sku, float $quantity): string
    {
        return <<<QUERY
mutation {
  addSimpleProductsToCart(input: {
    cart_id: "{$maskedQuoteId}", 
    cart_items: [
      {
        data: {
          quantity: {$quantity}
          sku: "{$sku}"
        }
      }                
    ]
  }) {
    cart {
      items {
        id
        quantity
        product {
          sku
        }
        prices {
          price {
           value
           currency
          }
          row_total {
           value
           currency
          }
          row_total_including_tax {
           value
           currency
          }
        }
      }
      shipping_addresses {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
        country {
          code
          label
        }
        __typename
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
