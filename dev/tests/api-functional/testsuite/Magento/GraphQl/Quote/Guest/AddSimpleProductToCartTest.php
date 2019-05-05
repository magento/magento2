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
 * Add simple product to cart testcases
 */
class AddSimpleProductToCartTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddSimpleProductToCart()
    {
        $sku = 'simple';
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery($maskedQuoteId, $sku, $qty);
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('cart', $response['addSimpleProductsToCart']);

        self::assertEquals($qty, $response['addSimpleProductsToCart']['cart']['items'][0]['qty']);
        self::assertEquals($sku, $response['addSimpleProductsToCart']['cart']['items'][0]['product']['sku']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testAddProductToNonExistentCart()
    {
        $sku = 'simple';
        $qty = 1;
        $maskedQuoteId = 'non_existent_masked_id';

        $query = $this->getQuery($maskedQuoteId, $sku, $qty);
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
  addSimpleProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cartItems: [
        {
          data: {
            qty: $qty
            sku: "$sku"
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
