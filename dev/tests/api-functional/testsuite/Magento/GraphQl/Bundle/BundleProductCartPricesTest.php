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
 * Test getting prices for bundle products
 */
class BundleProductCartPricesTest extends GraphQlAbstract
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
                'price' => 15,
                'price_type' => Price::PRICE_TYPE_FIXED,
                '_options' => ['$opt1$', '$opt2$']
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
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_2.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testBundleProductFixedPriceWithOptionsWithoutPrices()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        // price is the bundle product price as in this case the options don't have prices
        // specialPrice is the bundle product price * bundle product special price %
        // originalItemPriceProduct1 is the bundle product price
        // originalItemPriceProduct1 is with 10% discount as the special price
        $expectedResponse = $this->getExpectedResponse(15, 30, 30, 13.5, 27, 15, 13.5);

        $this->assertEquals($expectedResponse, $response);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product2.sku$',
                'price' => 10,
                'price_type' => LinkInterface::PRICE_TYPE_FIXED
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
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_2.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testBundleProductFixedPriceWithOneOptionFixedPrice()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        // price is the bundle product price + option fixed price
        // specialPrice is the bundle product price + option fixed price * bundle product special price %
        // originalItemPriceProduct1 is the bundle product price
        // originalItemPriceProduct1 is with 10% discount as the special price
        $expectedResponse = $this->getExpectedResponse(25, 50, 50, 22.5, 45, 25, 22.5);

        $this->assertEquals($expectedResponse, $response);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product1.sku$',
                'price' => 20,
                'price_type' => LinkInterface::PRICE_TYPE_FIXED
            ],
            'selection1'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product2.sku$',
                'price' => 10,
                'price_type' => LinkInterface::PRICE_TYPE_FIXED
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
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_2.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testBundleProductFixedPriceWithBothOptionsFixedPrice()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        // price is the bundle product price + options fixed prices
        // specialPrice is the bundle product price + options fixed prices * bundle product special price %
        // originalItemPriceProduct1 is the bundle product price
        // originalItemPriceProduct1 is with 10% discount as the special price
        $expectedResponse = $this->getExpectedResponse(45, 90, 90, 40.50, 81, 45, 40.5);

        $this->assertEquals($expectedResponse, $response);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product2.sku$',
                'price' => 20,
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
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_2.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testBundleProductFixedPriceWithOneOptionPercentPrice()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        // price is the (bundle product price * option percent price) + bundle product price
        // specialPrice is the (bundle product price * option percent price) +
        // bundle product price * bundle product special price %
        // originalItemPriceProduct1 is the bundle product price
        // originalItemPriceProduct1 is with 10% discount as the special price
        $expectedResponse = $this->getExpectedResponse(18, 36, 36, 16.20, 32.40, 18, 16.2);

        $this->assertEquals($expectedResponse, $response);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product1.sku$',
                'price' => 10,
                'price_type' => LinkInterface::PRICE_TYPE_PERCENT
            ],
            'selection1'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product2.sku$',
                'price' => 20,
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
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_2.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testBundleProductFixedPriceWithBothOptionsPercentPrices()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        // price is the (bundle product price * options percent price) + bundle product price
        // specialPrice is the (bundle product price * options percent price) +
        // bundle product price * bundle product special price %
        // originalItemPriceProduct1 is the bundle product price
        // originalItemPriceProduct1 is with 10% discount as the special price
        $expectedResponse = $this->getExpectedResponse(19.5, 39, 39, 17.55, 35.10, 19.5, 17.55);

        $this->assertEquals($expectedResponse, $response);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product1.sku$',
                'price' => 10,
                'price_type' => LinkInterface::PRICE_TYPE_FIXED
            ],
            'selection1'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product2.sku$',
                'price' => 20,
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
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_2.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testBundleProductFixedPriceWithOneOptionFixedAndOnePercentPrice()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        // price is the (bundle product price * option percent price) + bundle product price + option fixed price
        // specialPrice is the (bundle product price * option percent price) + bundle product price +
        // option fixed price * bundle product special price %
        // originalItemPriceProduct1 is the bundle product price
        // originalItemPriceProduct1 is with 10% discount as the special price
        $expectedResponse = $this->getExpectedResponse(28, 56, 56, 25.20, 50.40, 28, 25.2);

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
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testBundleProductDynamicPriceWithoutSpecialPrice()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $expectedResponse = [
            "cart" =>  [
                "items" => [
                    0 => [
                        "prices" => [
                            "price" => [
                                "value" => 30,
                                "currency" => "USD"
                            ],
                            "row_total" => [
                                "value" => 60,
                                "currency" => "USD"
                            ],
                            "original_row_total" => [
                                "value" => 60,
                                "currency" => "USD"
                            ],
                            "original_item_price" => [
                                "value" => 30,
                                "currency" => "USD"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedResponse, $response);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 20, 'special_price' => 15], 'product1'),
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
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testBundleProductDynamicPriceWithSpecialPrice()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $expectedResponse = [
            "cart" =>  [
                "items" => [
                    0 => [
                        "prices" => [
                            "price" => [
                                "value" => 25,
                                "currency" => "USD"
                            ],
                            "row_total" => [
                                "value" => 50,
                                "currency" => "USD"
                            ],
                            "original_row_total" => [
                                "value" => 60,
                                "currency" => "USD"
                            ],
                            "original_item_price" => [
                                "value" => 25, // product 1 special_price(15) + product 2 price (10)
                                "currency" => "USD"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Generates GraphQl query for get cart prices
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
      prices {
        price {
          value
          currency
        }
        row_total {
          value
          currency
        }
        original_row_total {
            value
            currency
        }
        original_item_price {
            value
            currency
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * @param $price
     * @param $rowTotal
     * @param $originalRowTotal
     * @param $specialPrice
     * @param $specialRowTotal
     * @param $originalItemPriceProduct1
     * @param $originalItemPriceProduct2
     * @return array[]
     */
    private function getExpectedResponse(
        $price,
        $rowTotal,
        $originalRowTotal,
        $specialPrice,
        $specialRowTotal,
        $originalItemPriceProduct1,
        $originalItemPriceProduct2
    ): array {
        return [
            "cart" =>  [
                "items" => [
                    0 => [
                        "prices" => [
                            "price" => [
                                "value" => $price,
                                "currency" => "USD"
                            ],
                            "row_total" => [
                                "value" => $rowTotal,
                                "currency" => "USD"
                            ],
                            "original_row_total" => [
                                "value" => $originalRowTotal,
                                "currency" => "USD"
                            ],
                            "original_item_price" => [
                                "value" => $originalItemPriceProduct1,
                                "currency" => "USD"
                            ]
                        ]
                    ],
                    1 => [
                        "prices" => [
                            "price" => [
                                "value" => $specialPrice,
                                "currency" => "USD"
                            ],
                            "row_total" => [
                                "value" => $specialRowTotal,
                                "currency" => "USD"
                            ],
                            "original_row_total" => [
                                "value" => $originalRowTotal,
                                "currency" => "USD"
                            ],
                            "original_item_price" => [
                                "value" => $originalItemPriceProduct2,
                                "currency" => "USD"
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
