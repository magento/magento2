<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Add simple product with custom options to cart using the unified mutation for adding different product types
 */
class AddSimpleProductToCartSingleMutationTest extends GraphQlAbstract
{
    /**
     * @var GetCustomOptionsWithUIDForQueryBySku
     */
    private $getCustomOptionsWithIDV2ForQueryBySku;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var GetCartItemOptionsFromUID
     */
    private $getCartItemOptionsFromUID;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteIdInterface;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->getCartItemOptionsFromUID = $objectManager->get(GetCartItemOptionsFromUID::class);
        $this->getCustomOptionsWithIDV2ForQueryBySku = $objectManager->get(
            GetCustomOptionsWithUIDForQueryBySku::class
        );
        $this->quoteIdToMaskedQuoteIdInterface = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test adding a simple product to the shopping cart with all supported
     * customizable options assigned
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_options.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddSimpleProductWithOptions()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $sku = 'simple';
        $qty = 1;

        $itemOptions = $this->getCustomOptionsWithIDV2ForQueryBySku->execute($sku);
        $decodedItemOptions = $this->getCartItemOptionsFromUID->execute($itemOptions);

        /* The type field is only required for assertions, it should not be present in query */
        foreach ($itemOptions['entered_options'] as &$enteredOption) {
            if (isset($enteredOption['type'])) {
                unset($enteredOption['type']);
            }
        }

        $productOptionsQuery = preg_replace(
            '/"([^"]+)"\s*:\s*/',
            '$1:',
            json_encode($itemOptions)
        );

        $query = $this->getAddToCartMutation($maskedQuoteId, $qty, $sku, trim($productOptionsQuery, '{}'));
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('items', $response['addProductsToCart']['cart']);
        self::assertCount($qty, $response['addProductsToCart']['cart']['items']);
        $customizableOptionsOutput =
            $response['addProductsToCart']['cart']['items'][0]['customizable_options'];

        foreach ($customizableOptionsOutput as $customizableOptionOutput) {
            $customizableOptionOutputValues = [];
            foreach ($customizableOptionOutput['values'] as $customizableOptionOutputValue) {
                $customizableOptionOutputValues[] =  $customizableOptionOutputValue['value'];

                $decodedOptionValue = base64_decode($customizableOptionOutputValue['customizable_option_value_uid']);
                $decodedArray = explode('/', $decodedOptionValue);
                if (count($decodedArray) === 2) {
                    self::assertEquals(
                        base64_encode('custom-option/' . $customizableOptionOutput['id']),
                        $customizableOptionOutputValue['customizable_option_value_uid']
                    );
                } elseif (count($decodedArray) === 3) {
                    self::assertEquals(
                        base64_encode(
                            'custom-option/'
                            . $customizableOptionOutput['id']
                            . '/'
                            . $customizableOptionOutputValue['value']
                        ),
                        $customizableOptionOutputValue['customizable_option_value_uid']
                    );
                } else {
                    self::fail('customizable_option_value_uid ');
                }
            }
            if (count($customizableOptionOutputValues) === 1) {
                $customizableOptionOutputValues = $customizableOptionOutputValues[0];
            }

            self::assertEquals(
                $decodedItemOptions[$customizableOptionOutput['id']],
                $customizableOptionOutputValues
            );

            self::assertEquals(
                base64_encode((string) 'custom-option/' . $customizableOptionOutput['id']),
                $customizableOptionOutput['customizable_option_uid']
            );
        }
    }

    /**
     * @param string $sku
     * @param string $message
     *
     * @dataProvider wrongSkuDataProvider
     *
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddProductWithWrongSku(string $sku, string $message)
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getAddToCartMutation($maskedQuoteId, 1, $sku, '');
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('user_errors', $response['addProductsToCart']);
        self::assertCount(1, $response['addProductsToCart']['user_errors']);
        self::assertEquals(
            $message,
            $response['addProductsToCart']['user_errors'][0]['message']
        );
    }

    /**
     * The test covers the case when upon adding available_qty + 1 to the shopping cart, the cart is being
     * cleared
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_without_custom_options.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddToCartWithQtyPlusOne()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $sku = 'simple-2';

        $query = $this->getAddToCartMutation($maskedQuoteId, 100, $sku, '');
        $response = $this->graphQlMutation($query);

        self::assertEquals(100, $response['addProductsToCart']['cart']['total_quantity']);

        $query = $this->getAddToCartMutation($maskedQuoteId, 1, $sku, '');
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('user_errors', $response['addProductsToCart']);
        self::assertEquals(
            'Not enough items for sale',
            $response['addProductsToCart']['user_errors'][0]['message']
        );
        self::assertEquals(100, $response['addProductsToCart']['cart']['total_quantity']);
    }

    /**
     * @param int $quantity
     * @param string $message
     *
     * @dataProvider wrongQuantityDataProvider
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_without_custom_options.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddProductWithWrongQuantity(int $quantity, string $message)
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $sku = 'simple-2';

        $query = $this->getAddToCartMutation($maskedQuoteId, $quantity, $sku, '');
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('user_errors', $response['addProductsToCart']);
        self::assertCount(1, $response['addProductsToCart']['user_errors']);

        self::assertEquals(
            $message,
            $response['addProductsToCart']['user_errors'][0]['message']
        );
    }

    /**
     * @dataProvider addProductNotAssignedToWebsiteDataProvider
     * @param string $cart
     * @param string $product
     * @param array $headerMap
     */
    #[
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(ProductFixture::class, ['website_ids' => [1]], as: 'product1'),
        DataFixture(ProductFixture::class, ['website_ids' => ['$website2.id$']], as: 'product2'),
        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(GuestCartFixture::class, as: 'cart2', scope: 'store2'),
    ]
    public function testAddProductNotAssignedToWebsite(string $cart, string $product, array $headerMap)
    {
        $sku = $this->fixtures->get($product)->getSku();
        $cartId = (int) $this->fixtures->get($cart)->getId();
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute($cartId);
        $query = $this->getAddToCartMutation($maskedQuoteId, 1, $sku);
        $headerMap = array_map(fn ($store) => $this->fixtures->get($store)?->getCode() ?? $store, $headerMap);
        $response = $this->graphQlMutation($query, [], '', $headerMap);
        self::assertEmpty($response['addProductsToCart']['cart']['items']);
        self::assertArrayHasKey('user_errors', $response['addProductsToCart']);
        self::assertCount(1, $response['addProductsToCart']['user_errors']);
        self::assertStringContainsString($sku, $response['addProductsToCart']['user_errors'][0]['message']);
        self::assertEquals('PRODUCT_NOT_FOUND', $response['addProductsToCart']['user_errors'][0]['code']);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testAddMultipleProductsToEmptyCart(): void
    {
        $product1 = $this->fixtures->get('product1');
        $product2 = $this->fixtures->get('product2');
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getAddMultipleProductsToCartAndReturnCartTotalsMutation(
            $maskedQuoteId,
            [
                [
                    'sku' => $product1->getSku(),
                    'quantity' => 2
                ],
                [
                    'sku' => $product2->getSku(),
                    'quantity' => 3
                ]
            ]
        );
        $response = $this->graphQlMutation($query);
        $result = $response['addProductsToCart'];
        self::assertEmpty($result['user_errors']);
        self::assertCount(2, $result['cart']['items']);

        $cartItem = $result['cart']['items'][0];
        self::assertEquals($product1->getSku(), $cartItem['product']['sku']);
        self::assertEquals(2, $cartItem['quantity']);
        self::assertEquals(10, $cartItem['prices']['price']['value']);
        self::assertEquals(20, $cartItem['prices']['row_total']['value']);

        $cartItem = $result['cart']['items'][1];
        self::assertEquals($product2->getSku(), $cartItem['product']['sku']);
        self::assertEquals(3, $cartItem['quantity']);
        self::assertEquals(10, $cartItem['prices']['price']['value']);
        self::assertEquals(30, $cartItem['prices']['row_total']['value']);

        $cartTotals = $result['cart']['prices'];
        self::assertEquals(50, $cartTotals['grand_total']['value']);
    }

    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(ProductFixture::class, as: 'p3'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$', 'qty' => 1]),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p2.id$', 'qty' => 1]),
    ]
    public function testAddMultipleProductsToNotEmptyCart(): void
    {
        $product1 = $this->fixtures->get('p1');
        $product2 = $this->fixtures->get('p2');
        $product3 = $this->fixtures->get('p3');
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getAddMultipleProductsToCartAndReturnCartTotalsMutation(
            $maskedQuoteId,
            [
                [
                    'sku' => $product1->getSku(),
                    'quantity' => 1
                ],
                [
                    'sku' => $product3->getSku(),
                    'quantity' => 1
                ]
            ]
        );
        $response = $this->graphQlMutation($query);
        $result = $response['addProductsToCart'];
        self::assertEmpty($result['user_errors']);
        self::assertCount(3, $result['cart']['items']);

        $cartItem = $result['cart']['items'][0];
        self::assertEquals($product1->getSku(), $cartItem['product']['sku']);
        self::assertEquals(2, $cartItem['quantity']);
        self::assertEquals(10, $cartItem['prices']['price']['value']);
        self::assertEquals(20, $cartItem['prices']['row_total']['value']);

        $cartItem = $result['cart']['items'][1];
        self::assertEquals($product2->getSku(), $cartItem['product']['sku']);
        self::assertEquals(1, $cartItem['quantity']);
        self::assertEquals(10, $cartItem['prices']['price']['value']);
        self::assertEquals(10, $cartItem['prices']['row_total']['value']);

        $cartItem = $result['cart']['items'][2];
        self::assertEquals($product3->getSku(), $cartItem['product']['sku']);
        self::assertEquals(1, $cartItem['quantity']);
        self::assertEquals(10, $cartItem['prices']['price']['value']);
        self::assertEquals(10, $cartItem['prices']['row_total']['value']);

        $cartTotals = $result['cart']['prices'];
        self::assertEquals(40, $cartTotals['grand_total']['value']);
    }

    #[
        DataFixture(ProductFixture::class, ['stock_item' => ['qty' => 1]], 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testAddMultipleProductsWithInsufficientStockToEmptyCart(): void
    {
        $product1 = $this->fixtures->get('product1');
        $product2 = $this->fixtures->get('product2');
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getAddMultipleProductsToCartAndReturnCartTotalsMutation(
            $maskedQuoteId,
            [
                [
                    'sku' => $product1->getSku(),
                    'quantity' => 2
                ],
                [
                    'sku' => $product2->getSku(),
                    'quantity' => 3
                ]
            ]
        );
        $response = $this->graphQlMutation($query);
        $result = $response['addProductsToCart'];
        self::assertCount(1, $result['user_errors']);
        self::assertEquals('INSUFFICIENT_STOCK', $result['user_errors'][0]['code']);

        self::assertCount(1, $result['cart']['items']);

        $cartItem = $result['cart']['items'][0];
        self::assertEquals($product2->getSku(), $cartItem['product']['sku']);
        self::assertEquals(3, $cartItem['quantity']);
        self::assertEquals(10, $cartItem['prices']['price']['value']);
        self::assertEquals(30, $cartItem['prices']['row_total']['value']);

        $cartTotals = $result['cart']['prices'];
        self::assertEquals(30, $cartTotals['grand_total']['value']);
    }

    /**
     * @return array
     */
    public static function addProductNotAssignedToWebsiteDataProvider(): array
    {
        return [
            ['cart1', 'product2', []],
            ['cart1', 'product2', ['Store' => 'default']],
            ['cart2', 'product1', ['Store' => 'store2']],
        ];
    }

    /**
     * @return array
     */
    public static function wrongSkuDataProvider(): array
    {
        return [
            'Non-existent SKU' => [
                'non-existent',
                'Could not find a product with SKU "non-existent"'
            ],
            'Empty SKU' => [
                '',
                'Could not find a product with SKU ""'
            ]
        ];
    }

    /**
     * @return array
     */
    public static function wrongQuantityDataProvider(): array
    {
        return [
            'More quantity than in stock' => [
                101,
                'Not enough items for sale'
            ],
            'Quantity equals zero' => [
                0,
                'The product quantity should be greater than 0'
            ]
        ];
    }

    /**
     * Returns GraphQl query string
     *
     * @param string $maskedQuoteId
     * @param int $qty
     * @param string $sku
     * @param string $customizableOptions
     * @return string
     */
    private function getAddToCartMutation(
        string $maskedQuoteId,
        int $qty,
        string $sku,
        string $customizableOptions = ''
    ): string {
        return <<<MUTATION
mutation {
    addProductsToCart(
        cartId: "{$maskedQuoteId}",
        cartItems: [
            {
                sku: "{$sku}"
                quantity: {$qty}
                {$customizableOptions}
            }
        ]
    ) {
        cart {
            total_quantity
            items {
                quantity
                ... on SimpleCartItem {
                    customizable_options {
                        label
                        id
                        customizable_option_uid
                          values {
                            value
                            customizable_option_value_uid
                            id
                        }
                    }
                }
            }
        },
        user_errors {
            code
            message
        }
    }
}
MUTATION;
    }

    /**
     * Returns GraphQl mutation for addProductsToCart with cart totals
     *
     * @param string $maskedQuoteId
     * @param array $cartItems
     * @return string
     */
    private function getAddMultipleProductsToCartAndReturnCartTotalsMutation(
        string $maskedQuoteId,
        array $cartItems
    ): string {
        $cartItemsQuery = preg_replace(
            '/"([^"]+)"\s*:\s*/',
            '$1:',
            json_encode($cartItems)
        );
        return  <<<MUTATION
mutation {
    addProductsToCart(
        cartId: "{$maskedQuoteId}",
        cartItems: $cartItemsQuery
    ) {
        cart {
            items {
              product {
                sku
              }
              quantity
              prices {
                price {
                  value
                  currency
                }
                row_total {
                  value
                  currency
                }
              }
            }
            prices {
              grand_total {
                value
                currency
              }
            }
        },
        user_errors {
            code
            message
        }
    }
}
MUTATION;
    }
}
