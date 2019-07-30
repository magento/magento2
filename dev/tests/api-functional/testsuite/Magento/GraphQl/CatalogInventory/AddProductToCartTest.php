<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogInventory;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Add simple product to cart testcases related to inventory
 */
class AddProductToCartTest extends GraphQlAbstract
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
     * @expectedException \Exception
     * @expectedExceptionMessage The requested qty is not available
     */
    public function testAddProductIfQuantityIsNotAvailable()
    {
        $sku = 'simple';
        $quantity = 200;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoConfigFixture default_store cataloginventory/item_options/max_sale_qty 5
     * @expectedException \Exception
     * @expectedExceptionMessage The most you may purchase is 5.
     */
    public function testAddMoreProductsThatAllowed()
    {
        $sku = 'custom-design-simple-product';
        $quantity = 7;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage Please enter a number greater than 0 in this field.
     */
    public function testAddSimpleProductToCartWithNegativeQuantity()
    {
        $sku = 'simple';
        $quantity = -2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testAddProductIfQuantityIsDecimal()
    {
        $sku = 'simple_product';
        $quantity = 0.2;

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);

        $this->expectExceptionMessage(
            "Could not add the product with SKU {$sku} to the shopping cart: The fewest you may purchase is 1"
        );
        $this->graphQlMutation($query);
    }

    /**
     * @param string $maskedQuoteId
     * @param string $sku
     * @param float $quantity
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $sku, float $quantity) : string
    {
        return <<<QUERY
mutation {  
  addSimpleProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}", 
      cart_items: [
        {
          data: {
            quantity: $quantity
            sku: "$sku"
          }
        }
      ]
    }
  ) {
    cart {
      items {
        quantity
      }
    }
  }
}
QUERY;
    }
}
