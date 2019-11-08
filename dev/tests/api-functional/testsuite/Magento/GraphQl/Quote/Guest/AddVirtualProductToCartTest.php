<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Exception;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Add virtual product to cart testcases
 */
class AddVirtualProductToCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/virtual_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testAddVirtualProductToCart()
    {
        $sku = 'virtual_product';
        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('cart', $response['addVirtualProductsToCart']);
        self::assertArrayHasKey('id', $response['addVirtualProductsToCart']['cart']);
        self::assertEquals($maskedQuoteId, $response['addVirtualProductsToCart']['cart']['id']);
        self::assertEquals($quantity, $response['addVirtualProductsToCart']['cart']['items'][0]['quantity']);
        self::assertEquals($sku, $response['addVirtualProductsToCart']['cart']['items'][0]['product']['sku']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Required parameter "cart_id" is missing
     */
    public function testAddVirtualProductToCartIfCartIdIsMissed()
    {
        $query = <<<QUERY
mutation {
  addSimpleProductsToCart(
    input: {
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

        $this->graphQlMutation($query);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Required parameter "cart_id" is missing
     */
    public function testAddVirtualProductToCartIfCartIdIsEmpty()
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

        $this->graphQlMutation($query);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Required parameter "cart_items" is missing
     */
    public function testAddVirtualProductToCartIfCartItemsAreMissed()
    {
        $query = <<<QUERY
mutation {
  addSimpleProductsToCart(
    input: {
      cart_id: "cart_id"
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

        $this->graphQlMutation($query);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Required parameter "cart_items" is missing
     */
    public function testAddVirtualProductToCartIfCartItemsAreEmpty()
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

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/virtual_product.php
     *
     * @expectedException Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testAddVirtualToNonExistentCart()
    {
        $sku = 'virtual_product';
        $quantity = 1;
        $maskedQuoteId = 'non_existent_masked_id';

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     *
     * @expectedException Exception
     * @expectedExceptionMessage Could not find a product with SKU "virtual_product"
     */
    public function testNonExistentProductToCart()
    {
        $sku = 'virtual_product';
        $quantity = 1;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $this->graphQlMutation($query);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/virtual_product.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     */
    public function testAddVirtualProductToCustomerCart()
    {
        $sku = 'virtual_product';
        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlMutation($query);
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
  addVirtualProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cart_items: [
        {
          data: {
            quantity: {$quantity}
            sku: "{$sku}"
          }
        }
      ]
    }
  ) {
    cart {
      id
      items {
        quantity
        product {
          sku
        }
      }
    }
  }
}
QUERY;
    }
}
