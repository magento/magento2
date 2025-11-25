<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;

/**
 * Get add to cart through GraphQl query and variables
 */
class AddProductsToCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->quoteIdToMaskedQuoteId = Bootstrap::getObjectManager()->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test addProductsToCart mutation by passing SKU Upper & Lower case
     *
     * @param string $sku
     * @dataProvider skuDataProvider
     * @throws NoSuchEntityException
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'Upper_And_Lower_Test_Prod']),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testAddProductsToCartWithSKUCaseInsensitive(string $sku): void
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());

        $query = $this->getAddToCartMutation($maskedQuoteId, $sku);
        $response = $this->graphQlMutation($query);
        $result = $response['addProductsToCart'];

        self::assertEmpty($result['user_errors']);
        self::assertCount(1, $result['cart']['items']);

        $cartItem = $result['cart']['items'][0];
        self::assertEquals('Upper_And_Lower_Test_Prod', $cartItem['product']['sku']);
        self::assertEquals(1, $cartItem['quantity']);
    }

    /**
     * @throws Exception
     */
    #[
        Config('cataloginventory/options/not_available_message', 2),
        DataFixture(ProductFixture::class, ['sku' => 'simple_10', 'price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 90], 'prodStock')
    ]
    public function testAddSimpleProductNotAvailableMessageInError()
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getAddToCartMutation($maskedQuoteId, 'simple_10', 100);
        $response = $this->graphQlMutation($query);
        self::assertEquals(
            [
                'code' => 'INSUFFICIENT_STOCK',
                'message' => 'Not enough items for sale'
            ],
            $response['addProductsToCart']['user_errors'][0]
        );
    }

    /**
     * @throws Exception
     */
    #[
        Config('cataloginventory/options/not_available_message', 1),
        DataFixture(ProductFixture::class, ['sku' => 'simple_10', 'price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 90], 'prodStock')
    ]
    public function testAddSimpleProductNotAvailableMessageInErrorWithQtyShown()
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getAddToCartMutation($maskedQuoteId, 'simple_10', 100);
        $response = $this->graphQlMutation($query);
        self::assertEquals(
            [
                'code' => 'INSUFFICIENT_STOCK',
                'message' => 'Only 90 of 100 available'
            ],
            $response['addProductsToCart']['user_errors'][0]
        );
    }

    /**
     * Data provider with sku in uppercase and lowercase
     *
     * @return array
     */
    public static function skuDataProvider(): array
    {
        return [
            'upper' => ['UPPER_AND_LOWER_TEST_PROD'],
            'lower' => ['upper_and_lower_test_prod'],
        ];
    }

    /**
     * Returns GraphQl mutation for (addProductsToCart) adding item to cart
     *
     * @param string $maskedQuoteId
     * @param string $sku
     * @param int $qty
     * @return string
     */
    private function getAddToCartMutation(string $maskedQuoteId, string $sku, int $qty = 1): string
    {
        return <<<MUTATION
mutation {
  addProductsToCart(
        cartId: "{$maskedQuoteId}",
        cartItems: [
            {
                sku: "{$sku}"
                quantity: $qty
            }
        ]
    ) {
    cart {
      id
      items {
        uid
        quantity
        product {
          sku
          name
        }
      }
    }
    user_errors {
      code
      message
    }
  }
}
MUTATION;
    }
}
