<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test getting priceV2 & original_price in BundleCartItem.bundle_options.values
 */
class BundleProductCartOriginalPricesTest extends GraphQlAbstract
{
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
        $this->quoteIdToMaskedQuoteIdInterface = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$'], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price',
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                '_options' => ['$opt1$', '$opt2$']
            ],
            'bundle_product_1'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price-special-price',
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                '_options' => ['$opt1$', '$opt2$'],
                'special_price' => 90 // it is the 90% of the original price
            ],
            'bundle_product_2'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']]
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_2.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']]
            ]
        )
    ]
    public function testBundleProductWithDynamicPrices()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $expectedResponse = $this->getExpectedResponse(
            20, //prod1 original price
            10, //prod2 original price
            20, //prod1 price is same as original price in bundle 1
            10, //prod2 price is same as original price in bundle 1
            18, //prod1 price is 90% of original price in bundle 2
            9 //prod2 price is 90% of original price in bundle 2
        );
        $this->assertEquals($expectedResponse, $response);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$', 'price' => 15], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$', 'price' => 8], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price',
                'price_type' => Price::PRICE_TYPE_FIXED,
                '_options' => ['$opt1$', '$opt2$'],
            ],
            'bundle_product_1'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price-special-price',
                'price_type' => Price::PRICE_TYPE_FIXED,
                '_options' => ['$opt1$', '$opt2$'],
                'special_price' => 90 // it is the 90% of the original price
            ],
            'bundle_product_2'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']]
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_2.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']]
            ]
        )
    ]
    public function testBundleProductWithFixedPrices()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $expectedResponse = $this->getExpectedResponse(
            15, //fixed price set for selection 1
            8, //fixed price set for selection 2
            15, //prod1 price is same as fixed price in bundle 1
            8, //prod2 price is same as fixed price in bundle 1
            13.5, //prod1 price is 90% of fixed price in bundle 2
            7.2 //prod2 price is 90% of fixed price in bundle 2
        );
        $this->assertEquals($expectedResponse, $response);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product1.sku$',
                'price' => 90, //90% of bundle price
                'price_type' => LinkInterface::PRICE_TYPE_PERCENT
            ],
            'selection1'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product2.sku$',
                'price' => 80, //80% of bundle price
                'price_type' => LinkInterface::PRICE_TYPE_PERCENT
            ],
            'selection2'
        ),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price',
                'price' => 15,
                'price_type' => Price::PRICE_TYPE_FIXED,
                '_options' => ['$opt1$', '$opt2$'],
            ],
            'bundle_product_1'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price-special-price',
                'price' => 15,
                'price_type' => Price::PRICE_TYPE_FIXED,
                '_options' => ['$opt1$', '$opt2$'],
                'special_price' => 90 // it is the 90% of the original price
            ],
            'bundle_product_2'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']]
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_2.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']]
            ]
        )
    ]
    public function testBundleProductWithPercentagePrices()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $expectedResponse = $this->getExpectedResponse(
            13.5, //90% of selection 1
            12, //80% of selection 1
            13.5, //prod1 price is same as percentage price in bundle 1
            12, //prod2 price is same as percentage price in bundle 1
            12.15, //prod1 price is 90% of percentage price in bundle 2
            10.8 //prod2 price is 90% of percentage price in bundle 2
        );
        $this->assertEquals($expectedResponse, $response);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$'], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-dynamic-price',
                '_options' => ['$opt1$', '$opt2$'],
            ],
            'bundle_product_1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']]
            ]
        )
    ]
    public function testBundleProductWithoutSpecialPrice()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $expectedResponse = $this->getExpectedResponseSingleBundle(
            20, //prod1 original price
            10 //prod2 original price
        );

        $this->assertEquals($expectedResponse, $response);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20, 'special_price' => 15], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10, 'special_price' => 8], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$'], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-dynamic-price',
                '_options' => ['$opt1$', '$opt2$'],
            ],
            'bundle_product_1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']]
            ]
        )
    ]
    public function testBundleProductWithSpecialPrice()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $expectedResponse = $this->getExpectedResponseSingleBundle(
            15, //prod1 special price
            8 //prod2 special price
        );

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Generates GraphQl query for getting cart prices (priceV2 & original_price) of bundle products
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    items {
      ... on BundleCartItem {
        bundle_options {
          values {
            priceV2 {
              value
              currency
            }
            original_price {
              value
              currency
            }
          }
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Returns expected result with priceV2 & original_price for multiple bundles in cart
     *
     * @param $item1Option1PriceV2
     * @param $item2Option1PriceV2
     * @param $item1Option2PriceV2
     * @param $item2Option2PriceV2
     * @return array[]
     */
    private function getExpectedResponse(
        $product1OriginalPrice,
        $product2OriginalPrice,
        $item1Option1PriceV2,
        $item2Option1PriceV2,
        $item1Option2PriceV2,
        $item2Option2PriceV2,
    ): array {
        return [
            "cart" =>  [
                "items" => [
                    0 => [
                        "bundle_options" => [
                            0 => [
                                'values' => [
                                    0 => [
                                        "priceV2" => [
                                            "value" => $item1Option1PriceV2,
                                            "currency" => "USD"
                                        ],
                                        "original_price" => [
                                            "value" => $product1OriginalPrice,
                                            "currency" => "USD"
                                        ]
                                    ]
                                ]
                            ],
                            1 => [
                                'values' => [
                                    0 => [
                                        "priceV2" => [
                                            "value" => $item2Option1PriceV2,
                                            "currency" => "USD"
                                        ],
                                        "original_price" => [
                                            "value" => $product2OriginalPrice,
                                            "currency" => "USD"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    1 => [
                        "bundle_options" => [
                            0 => [
                                'values' => [
                                    0 => [
                                        "priceV2" => [
                                            "value" => $item1Option2PriceV2,
                                            "currency" => "USD"
                                        ],
                                        "original_price" => [
                                            "value" => $product1OriginalPrice,
                                            "currency" => "USD"
                                        ]
                                    ]
                                ]
                            ],
                            1 => [
                                'values' => [
                                    0 => [
                                        "priceV2" => [
                                            "value" => $item2Option2PriceV2,
                                            "currency" => "USD"
                                        ],
                                        "original_price" => [
                                            "value" => $product2OriginalPrice,
                                            "currency" => "USD"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns expected result with priceV2 & original_price for single bundles in cart
     *
     * @param $item1SpecialPrice
     * @param $item2SpecialPrice
     * @return array[]
     */
    private function getExpectedResponseSingleBundle(
        $item1SpecialPrice,
        $item2SpecialPrice
    ): array {
        return [
            "cart" =>  [
                "items" => [
                    0 => [
                        "bundle_options" => [
                            0 => [
                                'values' => [
                                    0 => [
                                        "priceV2" => [
                                            "value" => $item1SpecialPrice,
                                            "currency" => "USD"
                                        ],
                                        "original_price" => [
                                            "value" => 20,
                                            "currency" => "USD"
                                        ]
                                    ]
                                ]
                            ],
                            1 => [
                                'values' => [
                                    0 => [
                                        "priceV2" => [
                                            "value" => $item2SpecialPrice,
                                            "currency" => "USD"
                                        ],
                                        "original_price" => [
                                            "value" => 10,
                                            "currency" => "USD"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
