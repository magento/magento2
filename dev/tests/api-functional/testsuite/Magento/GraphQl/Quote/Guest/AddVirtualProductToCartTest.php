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
    protected function setUp(): void
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
     */
    public function testAddVirtualProductToCartIfCartIdIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "cart_id" is missing');

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
     */
    public function testAddVirtualProductToCartIfCartItemsAreEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "cart_items" is missing');

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
     */
    public function testAddVirtualToNonExistentCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a cart with ID "non_existent_masked_id"');

        $sku = 'virtual_product';
        $quantity = 1;
        $maskedQuoteId = 'non_existent_masked_id';

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     *
     */
    public function testNonExistentProductToCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a product with SKU "virtual_product"');

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
