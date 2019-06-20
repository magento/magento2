<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Add configurable product to cart testcases
 */
class AddConfigurableProductToCartTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductToCart()
    {
        $variantSku = 'simple_41';
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery($maskedQuoteId, $variantSku, $qty);
        $response = $this->graphQlMutation($query);

        $cartItems = $response['addConfigurableProductsToCart']['cart']['items'];
        self::assertEquals($qty, $cartItems[0]['qty']);
        self::assertEquals($variantSku, $cartItems[0]['product']['sku']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage The requested qty is not available
     */
    public function testAddProductIfQuantityIsNotAvailable()
    {
        $variantSku = 'simple_41';
        $qty = 200;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery($maskedQuoteId, $variantSku, $qty);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Framework/Search/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage Product that you are trying to add is not available.
     */
    public function testAddOutOfStockProduct()
    {
        $variantSku = 'simple_1010';
        $qty = 1;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery($maskedQuoteId, $variantSku, $qty);
        $this->graphQlMutation($query);
    }

    /**
     * @param string $maskedQuoteId
     * @param string $variantSku
     * @param int $qty
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $variantSku, int $qty): string
    {
        return <<<QUERY
mutation {
  addConfigurableProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cartItems: [
        {
          variant_sku: "{$variantSku}"
          data: {
            qty: {$qty}
            sku: "{$variantSku}"
          }
        }
      ]
    }
  ) {
    cart {
      items {
        id
        qty
        product {
          name
          sku
        }
        ... on ConfigurableCartItem {
          configurable_options {
            option_label
          }
        }
    }
  }
  }
}
QUERY;
    }
}
