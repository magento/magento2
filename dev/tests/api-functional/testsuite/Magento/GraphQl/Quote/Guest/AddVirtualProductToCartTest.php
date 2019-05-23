<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

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
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $sku, $qty);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('cart', $response['addVirtualProductsToCart']);
        self::assertEquals($qty, $response['addVirtualProductsToCart']['cart']['items'][0]['qty']);
        self::assertEquals($sku, $response['addVirtualProductsToCart']['cart']['items'][0]['product']['sku']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/virtual_product.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testAddVirtualToNonExistentCart()
    {
        $sku = 'virtual_product';
        $qty = 1;
        $maskedQuoteId = 'non_existent_masked_id';

        $query = $this->getQuery($maskedQuoteId, $sku, $qty);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a product with SKU "virtual_product"
     */
    public function testNonExistentProductToCart()
    {
        $sku = 'virtual_product';
        $qty = 1;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $sku, $qty);
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
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $qty);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlMutation($query);
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
  addVirtualProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cartItems: [
        {
          data: {
            qty: {$qty}
            sku: "{$sku}"
          }
        }
      ]
    }
  ) {
    cart {
      items {
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
}
