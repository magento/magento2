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
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;

/**
 * Get add to cart through GraphQl query and variables
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @throws NoSuchEntityException
     * @throws Exception
     */
    #[DataProvider('skuDataProvider')]
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
     * Validate that addProductsToCart returns a specific error code for cart validation errors
     *
     * Each validation message raised while adding a product to the cart must be mapped to a specific
     * CartUserInputErrorType code (via AddProductsToCartError mapping in Magento_Quote di.xml) instead of
     * the UNDEFINED fallback, so that clients can rely on the code to decide how to handle the error.
     *
     * Fixtures:
     *  - simple_restricted_qty: "Maximum Qty Allowed in Shopping Cart" is set to 5
     *  - simple_qty_increments: "Qty Increments" is enabled and set to 3
     *
     * @param string $sku SKU of the product to add to the cart
     * @param float $quantity Quantity to add to the cart
     * @param array $selectedOptions Base64-encoded selected option UIDs to send with the cart item
     * @param array $expectedError Expected first user error, with 'code' and 'message' keys
     * @throws Exception
     */
    #[DataProvider('cartValidationErrorDataProvider')]
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple_restricted_qty',
                'extension_attributes' => [
                    'stock_item' => [
                        'use_config_max_sale_qty' => false,
                        'max_sale_qty' => 5,
                    ],
                ],
            ],
            as: 'product'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple_qty_increments',
                'extension_attributes' => [
                    'stock_item' => [
                        'use_config_enable_qty_inc' => false,
                        'enable_qty_increments' => true,
                        'use_config_qty_increments' => false,
                        'qty_increments' => 3,
                    ],
                ],
            ],
            as: 'productWithIncrements'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testAddProductsToCartReturnsSpecificErrorCode(
        string $sku,
        float $quantity,
        array $selectedOptions,
        array $expectedError
    ): void {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $selectedOptionsInput = $selectedOptions
            ? 'selected_options: ["' . implode('","', $selectedOptions) . '"]'
            : '';
        $query = <<<MUTATION
mutation {
  addProductsToCart(
    cartId: "{$maskedQuoteId}",
    cartItems: [{ sku: "{$sku}", quantity: {$quantity} {$selectedOptionsInput} }]
  ) {
    user_errors {
      code
      message
    }
  }
}
MUTATION;
        $response = $this->graphQlMutation($query);

        self::assertNotEmpty($response['addProductsToCart']['user_errors']);
        self::assertEquals($expectedError, $response['addProductsToCart']['user_errors'][0]);
    }

    /**
     * Data provider with cart validation errors and their expected error codes
     *
     * Each case contains: product SKU, quantity, selected option UIDs and the expected user error.
     *
     * @return array
     */
    public static function cartValidationErrorDataProvider(): array
    {
        return [
            'quantity above maximum allowed in cart' => [
                'simple_restricted_qty',
                6,
                [],
                [
                    'code' => 'INSUFFICIENT_STOCK',
                    'message' => 'The requested qty exceeds the maximum qty allowed in shopping cart',
                ],
            ],
            'decimal quantity' => [
                'simple_restricted_qty',
                1.5,
                [],
                [
                    'code' => 'INVALID_PARAMETER_VALUE',
                    'message' => 'You cannot use decimal quantity for this product.',
                ],
            ],
            'zero quantity' => [
                'simple_restricted_qty',
                0,
                [],
                [
                    'code' => 'INVALID_PARAMETER_VALUE',
                    'message' => 'The product quantity should be greater than 0',
                ],
            ],
            'quantity not matching qty increments' => [
                'simple_qty_increments',
                4,
                [],
                [
                    'code' => 'INVALID_PARAMETER_VALUE',
                    'message' => 'You can buy this product only in quantities of 3 at a time.',
                ],
            ],
            'wrong format of selected option' => [
                'simple_restricted_qty',
                1,
                [base64_encode('custom-option/1')],
                [
                    'code' => 'INVALID_PARAMETER_VALUE',
                    'message' => 'Wrong format of the entered option data',
                ],
            ],
        ];
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
