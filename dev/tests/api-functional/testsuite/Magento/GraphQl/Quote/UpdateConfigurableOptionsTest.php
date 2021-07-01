<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for configurable options update
 */
class UpdateConfigurableOptionsTest extends GraphQlAbstract
{
    /**
     * Test prices with applied sales rules for joined mutation for configurable options update
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_qty_more_than_2_items.php
     */
    public function testUpdateConfigurableOptions(): void
    {
        $response = $this->graphQlMutation($this->getCartMutation());
        $maskedCartId = $response['createEmptyCart'];

        $parentSku = 'configurable';
        $variant1 = 'simple_20';
        $variant2 = 'simple_10';

        $response = $this->graphQlMutation(
            $this->getAddConfigurableProductsToCartMutation($maskedCartId, $variant1, $parentSku, 2)
        );

        self::assertNotEmpty($response['addConfigurableProductsToCart']['cart']['items']);
        $cartItemId = current($response['addConfigurableProductsToCart']['cart']['items'])['id'];

        $response = $this->graphQlMutation(
            $this->getMainQuery($maskedCartId, (int)$cartItemId, $parentSku, $variant2, 1)
        );

        self::assertEquals($maskedCartId, $response['addConfigurableProductsToCart']['cart']['id']);
        self::assertEquals(3, $response['addConfigurableProductsToCart']['cart']['total_quantity']);
        self::assertEquals(
            45,
            $response['addConfigurableProductsToCart']['cart']['prices']['grand_total']['value']
        );
        self::assertEquals(
            'USD',
            $response['addConfigurableProductsToCart']['cart']['prices']['grand_total']['currency']
        );

        self::assertEquals($maskedCartId, $response['removeItemFromCart']['cart']['id']);
        self::assertEquals(1, $response['removeItemFromCart']['cart']['total_quantity']);
        self::assertEquals(10, $response['removeItemFromCart']['cart']['prices']['grand_total']['value']);
        self::assertEquals('USD', $response['removeItemFromCart']['cart']['prices']['grand_total']['currency']);
    }

    /**
     * Get masked cart id mutation
     *
     * @return string
     */
    private function getCartMutation(): string
    {
        return <<<MUTATION
mutation {
  createEmptyCart
}
MUTATION;
    }

    /**
     * Get addConfigurableProductsToCart mutation
     *
     * @param string $maskedCartId
     * @param string $sku
     * @param string $parentSku
     * @param int $qty
     * @return string
     */
    private function getAddConfigurableProductsToCartMutation(
        string $maskedCartId,
        string $sku,
        string $parentSku,
        int $qty
    ): string {
        return <<<MUTATION
mutation {
  addConfigurableProductsToCart(
    input: {
        cart_id: "{$maskedCartId}"
        cart_items: [{data: {quantity: {$qty}, sku: "{$sku}"}, parent_sku: "{$parentSku}"}]
    }
  )
  {
    cart {
      items {
        id
      }
    }
  }
}
MUTATION;
    }

    /**
     * Get joined mutation for updating configurable items
     *
     * @param string $maskedCartId
     * @param int $cartItemId
     * @param string $parentSku
     * @param string $variantSku
     * @param int $quantity
     * @return string
     */
    private function getMainQuery(
        string $maskedCartId,
        int $cartItemId,
        string $parentSku,
        string $variantSku,
        int $quantity
    ): string {
        return <<<MUTATION
mutation UpdateConfigurableOptions {
  addConfigurableProductsToCart(
    input: {
      cart_id: "{$maskedCartId}"
      cart_items: [
        {
          data: { quantity: {$quantity}, sku: "{$variantSku}" }
          parent_sku: "{$parentSku}"
        }
      ]
    }
  ) {
    cart {
      id
      total_quantity
      available_payment_methods {
        code
        title
      }
      prices {
        grand_total {
          value
          currency
        }
      }
    }
  }
  removeItemFromCart(input: { cart_id: "{$maskedCartId}", cart_item_id: {$cartItemId} }) {
    cart {
      id
      total_quantity
      available_payment_methods {
        code
        title
      }
      prices {
        grand_total {
          value
          currency
        }
      }
    }
  }
}
MUTATION;
    }
}
