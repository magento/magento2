<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Indexer\Test\Fixture\Indexer;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\GraphQl\Quote\GetCustomOptionsWithUIDForQueryBySku;
use Magento\Catalog\Test\Fixture\Product;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;

/**
 * Test adding purchase order items to the shopping cart
 */
class AddProductWithOptionsToCartTest extends GraphQlAbstract
{
    #[
        DataFixture(GuestCart::class, as: 'quote'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
        DataFixture(
            Product::class,
            [
            'sku' => 'simple1',
            'options' => [
                [
                    'title' => 'option1',
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    'values' => [
                        [
                            'title' => 'option1_value1'
                        ],
                        [
                            'title' => 'option1_value2'
                        ]
                    ]
                ]
            ]
            ],
            'product1'
        ),
        DataFixture(Indexer::class, as: 'indexer')
    ]
    public function testAddProductWithOptionsResponse()
    {
        $uidEncoder = Bootstrap::getObjectManager()->create(Uid::class);

        $cartId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $product = DataFixtureStorageManager::getStorage()->get('product1');
        /* @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $sku = $product->getSku();
        $option = $product->getOptions();

        $productOptions = [];
        foreach ($product->getOptions()[0]->getValues() as $value) {
            $productOptions[] = [
                'title' => $value->getTitle(),
                'uid' => $uidEncoder->encode(
                    'custom-option' . '/' . $option[0]->getData()['option_id'] . '/' . $value->getId()
                )
            ];
        }
        $optionUid = $uidEncoder->encode(
            'custom-option' . '/' . $option[0]->getData()['option_id']
        );

        // Assert if product options for item added to the cart
        // are present in mutation response after product with selected option was added
        $mutation = $this->getAddProductWithOptionMutation($cartId, $sku, $productOptions[0]['uid']);
        $response = $this->graphQlMutation($mutation);

        $this->assertArrayHasKey('items', $response['addProductsToCart']['cart']);
        $this->assertCount(1, $response['addProductsToCart']['cart']['items']);
        $this->assertArrayHasKey('options', $response['addProductsToCart']['cart']['items'][0]['product']);

        $this->assertEquals(
            $response['addProductsToCart']['cart']['items'],
            $this->getExpectedResponse($optionUid, $productOptions)
        );

        // Assert if product options for item in the cart are present in the response
        $query = $this->getCartQuery($cartId);
        $response = $this->graphQlQuery($query);
        $this->assertEquals(
            $response['cart']['items'],
            $this->getExpectedResponse($optionUid, $productOptions)
        );
    }

    #[
        DataFixture(GuestCart::class, as: 'quote'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
        DataFixture(
            Product::class,
            [
                'sku' => 'simple1',
                'options' => [
                    [
                        'title' => 'option1',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    ]
                ]
            ],
            'product1'
        ),
        DataFixture(Indexer::class, as: 'indexer')
    ]
    public function testAddSameProductWithDifferentOptionValues()
    {
        $uidEncoder = Bootstrap::getObjectManager()->create(Uid::class);

        $cartId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $product = DataFixtureStorageManager::getStorage()->get('product1');
        /* @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $sku = $product->getSku();
        $option = $product->getOptions();
        $optionUid = $uidEncoder->encode(
            'custom-option' . '/' . $option[0]->getData()['option_id']
        );

        // Assert if product options for item added to the cart
        // are present in mutation response after product with selected option was added
        $mutation = $this->getAddProductWithDifferentOptionValuesMutation(
            $cartId,
            $sku,
            $optionUid
        );
        $response = $this->graphQlMutation($mutation);

        $this->assertArrayHasKey('items', $response['addProductsToCart']['cart']);
        $this->assertCount(2, $response['addProductsToCart']['cart']['items']);
        $this->assertArrayHasKey('customizable_options', $response['addProductsToCart']['cart']['items'][0]);

        $this->assertEquals(
            $response['addProductsToCart']['cart']['items'],
            $this->getExpectedResponseForDifferentOptionValues($optionUid, $sku)
        );

        // Assert if product options for item in the cart are present in the response
        $query = $this->getCartQueryForDifferentOptionValues($cartId);
        $response = $this->graphQlQuery($query);
        $this->assertEquals(
            $this->getExpectedResponseForDifferentOptionValues($optionUid, $sku),
            $response['cart']['items']
        );
    }

    #[
        DataFixture(GuestCart::class, as: 'quote'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
        DataFixture(
            Product::class,
            [
            'sku' => 'simple1',
            'options' => [
                [
                    'title' => 'test_option_code_1',
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    'is_require' => true,
                    'sort_order' => 1,
                    'price' => -10.0,
                    'price_type' => 'fixed',
                    'sku' => 'sku1',
                    'max_characters' => 10,
                ],
                [
                    'title' => 'area option',
                    'type' => 'area',
                    'is_require' => true,
                    'sort_order' => 2,
                    'price' => 20.0,
                    'price_type' => 'percent',
                    'sku' => 'sku2',
                    'max_characters' => 20
                ],
                [
                    'title' => 'drop_down option',
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    'is_require' => false,
                    'sort_order' => 4,
                    'values' => [
                        [
                            'title' => 'drop_down option 1',
                            'price' => 10,
                            'price_type' => 'fixed',
                            'sku' => 'drop_down option 1 sku',
                            'sort_order' => 1,
                        ],
                        [
                            'title' => 'drop_down option 2',
                            'price' => 20,
                            'price_type' => 'fixed',
                            'sku' => 'drop_down option 2 sku',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                [
                    'title' => 'multiple option',
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                    'is_require' => false,
                    'sort_order' => 5,
                    'values' => [
                        [
                            'title' => 'multiple option 1',
                            'price' => 10,
                            'price_type' => 'fixed',
                            'sku' => 'multiple option 1 sku',
                            'sort_order' => 1,
                        ],
                        [
                            'title' => 'multiple option 2',
                            'price' => 20,
                            'price_type' => 'fixed',
                            'sku' => 'multiple option 2 sku',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                [
                    'title' => 'date option',
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DATE,
                    'price' => 80.0,
                    'price_type' => 'fixed',
                    'sku' => 'date option sku',
                    'is_require' => false,
                    'sort_order' => 6
                ]
            ]
            ],
            'product1'
        ),
        DataFixture(Indexer::class, as: 'indexer')
    ]
    public function testAddSimpleProductWithCustomOptions()
    {
        $getCustomOptionsWithIDV2ForQueryBySku = Bootstrap::getObjectManager()->get(
            GetCustomOptionsWithUIDForQueryBySku::class
        );
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();

        $sku = 'simple1';
        $qty = 1;

        $itemOptions = $getCustomOptionsWithIDV2ForQueryBySku->execute($sku);

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

        $query = $this->getAddProductsToCartMutation($maskedQuoteId, $qty, $sku, trim($productOptionsQuery, '{}'));
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('items', $response['addProductsToCart']['cart']);
        self::assertCount($qty, $response['addProductsToCart']['cart']['items']);
        self::assertNotEmpty($response['addProductsToCart']['cart']['items'][0]['customizable_options']);
    }

    /**
     * Returns mutation for the test
     *
     * @param string $cartId
     * @param string $sku
     * @param string $selectedOptionUid
     * @return string
     */
    private function getAddProductWithOptionMutation(string $cartId, string $sku, string $selectedOptionUid): string
    {
        return <<<QRY
mutation {
    addProductsToCart(
        cartId: "{$cartId}"
        cartItems: [
            {
                quantity:1
                sku: "{$sku}"
                selected_options: ["{$selectedOptionUid}"]
            }
        ]
    ) {
        cart {
        id
            items {
            quantity
            product {
                sku
                ... on CustomizableProductInterface {
                    options {
                        title
                      uid
                        ... on CustomizableDropDownOption {
                            value {
                                title
                              uid
                            }
                        }
                    }
                }
            }
            }
        }
    }
}
QRY;
    }

    /**
     * Returns mutation for the test with different option values
     *
     * @param string $cartId
     * @param string $sku
     * @param string $optionUid
     * @return string
     */
    private function getAddProductWithDifferentOptionValuesMutation(
        string $cartId,
        string $sku,
        string $optionUid
    ): string {
        return <<<QRY
mutation {
    addProductsToCart(
        cartId: "{$cartId}"
        cartItems: [
            {
                quantity:1
                sku: "{$sku}"
                entered_options: [{
                    uid:"{$optionUid}",
                    value:"value1"
                }]
            }
            {
                quantity:1
                sku: "{$sku}"
                entered_options: [{
                    uid:"{$optionUid}",
                    value:"value2"
                }]
            }
        ]
    ) {
        cart {
        id
            items {
                quantity
                product {
                    sku
                }
                ... on SimpleCartItem {
                    customizable_options {
                        customizable_option_uid
                        label
                        values {
                            customizable_option_value_uid
                            value
                        }
                    }
                }
            }
        }
    }
}
QRY;
    }

    /**
     * Returns query to get cart with information about item and associated product with options
     *
     * @param string $cartId
     * @return string
     */
    private function getCartQuery(string $cartId): string
    {
        return <<<QRY
query {
    cart(cart_id: "{$cartId}")
    {
        items {
            quantity
            product {
                sku
                ... on CustomizableProductInterface {
                    options {
                        title
                        uid
                        ... on CustomizableDropDownOption {
                            value {
                                title
                                uid
                            }
                        }
                    }
                }
            }
        }
    }
}
QRY;
    }

    /**
     * Returns query to get cart with information about item and associated product with different option values
     *
     * @param string $cartId
     * @return string
     */
    private function getCartQueryForDifferentOptionValues(string $cartId): string
    {
        return <<<QRY
query {
    cart(cart_id: "{$cartId}")
    {
        items {
            quantity
            product {
                sku
            }
            ... on SimpleCartItem {
                customizable_options {
                    customizable_option_uid
                    label
                    values {
                        customizable_option_value_uid
                        value
                    }
                }
            }
        }
    }
}
QRY;
    }

    /**
     * Formats and returns expected response
     *
     * @param string $selectedOptionUid
     * @param array $productOptions
     * @return array
     */

    /**
     * Returns GraphQl query string
     *
     * @param string $maskedQuoteId
     * @param int $qty
     * @param string $sku
     * @param string $customizableOptions
     * @return string
     */
    private function getAddProductsToCartMutation(
        string $maskedQuoteId,
        int $qty,
        string $sku,
        string $customizableOptions = '',
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
        items {
          product {
            name
            sku
          }
          ... on SimpleCartItem {
            customizable_options {
              label
              customizable_option_uid
              values {
                value
                customizable_option_value_uid
              }
            }
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

    /**
     * Returns formatted response
     *
     * @param string $selectedOptionUid
     * @param array $productOptions
     * @return array
     */
    private function getExpectedResponse(string $selectedOptionUid, array $productOptions): array
    {
        return [
            [
                "quantity" => 1,
                "product" =>
                    [
                        "sku" => "simple1",
                        "options" =>
                            [
                                [
                                    "title" => "option1",
                                    "uid" => "{$selectedOptionUid}",
                                    "value" =>
                                        [
                                            [
                                                "title" => "option1_value1",
                                                "uid" => "{$productOptions[0]['uid']}",
                                            ],
                                            [
                                                "title" => "option1_value2",
                                                "uid" => "{$productOptions[1]['uid']}",
                                            ]
                                        ]
                                ]
                            ]
                    ]
            ]
        ];
    }

    /**
     * Returns formatted response for test with different option values
     *
     * @param string $selectedOptionUid
     * @param array $productOptions
     * @return array
     */
    private function getExpectedResponseForDifferentOptionValues(string $optionUid, string $sku): array
    {
        return [
            0 => [
                "quantity" => 1,
                "product" => ["sku" => "{$sku}"],
                "customizable_options" => [
                    0 => [
                        "customizable_option_uid" => "{$optionUid}",
                        "label" => "option1",
                        "values" => [
                            0 => [
                                "customizable_option_value_uid" => "{$optionUid}",
                                "value" => "value1"
                            ]
                        ]
                    ]
                ]
            ],
            1 => [
                "quantity" => 1,
                "product" => ["sku" => "{$sku}"],
                "customizable_options" => [
                    0 => [
                        "customizable_option_uid" => "{$optionUid}",
                        "label" => "option1",
                        "values" => [
                            0 => [
                                "customizable_option_value_uid" => "{$optionUid}",
                                "value" => "value2"
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
