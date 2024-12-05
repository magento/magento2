<?php
/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2024 Adobe
 *  All Rights Reserved.
 *
 *  NOTICE: All information contained herein is, and remains
 *  the property of Adobe and its suppliers, if any. The intellectual
 *  and technical concepts contained herein are proprietary to Adobe
 *  and its suppliers and are protected by all applicable intellectual
 *  property laws, including trade secret and copyright laws.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from Adobe.
 *  ************************************************************************
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\DataObject;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class InsufficientStockErrorTest extends GraphQlAbstract
{
    private const SKU = 'test-product';

    #[
        Config('cataloginventory/options/not_available_message', 1),
        DataFixture(ProductFixture::class, ['sku' => self::SKU, 'price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 99]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testInsufficientStockError(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->mutationAddProduct($maskedQuoteId, self::SKU, 200);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        $this->assertEquals(
            $responseDataObject->getData('addProductsToCart/user_errors/0/__typename'),
            'InsufficientStockError'
        );

        $this->assertEquals(
            $responseDataObject->getData('addProductsToCart/user_errors/0/quantity'),
            100
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 0),
        DataFixture(ProductFixture::class, ['sku' => self::SKU, 'price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 99]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testCartUserInputError(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->mutationAddProduct($maskedQuoteId, self::SKU, 200);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        $this->assertEquals(
            $responseDataObject->getData('addProductsToCart/user_errors/0/__typename'),
            'CartUserInputError'
        );

        $this->assertArrayNotHasKey(
            'quantity',
            $responseDataObject->getData('addProductsToCart/user_errors')
        );
    }

    private function mutationAddProduct(string $cartId, string $sku, int $qty = 1): string
    {
        return <<<QUERY
mutation {
  addProductsToCart(
    cartId: "{$cartId}",
    cartItems: [
    {
      sku: "{$sku}"
      quantity: $qty
    }]
  ) {
    cart {
      itemsV2 {
        items {
        product {
            name
          }
          quantity
        }
        total_count
      }
    }
    user_errors {
      message
      __typename
      ...InsufficientStockErrorFragment
    }
  }
}
fragment InsufficientStockErrorFragment on InsufficientStockError {
      quantity
}
QUERY;
    }
}
