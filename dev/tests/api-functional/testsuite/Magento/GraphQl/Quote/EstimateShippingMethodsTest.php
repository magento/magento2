<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\OfflineShipping\Test\Fixture\TablerateFixture;

/**
 * Test for guest shipping methods estimate costs
 */
class EstimateShippingMethodsTest extends GraphQlAbstract
{
    #[
        ConfigFixture('shipping/origin/country_id', 'US'),
        ConfigFixture('general/store_information/country_id', 'US'),
        ConfigFixture('carriers/flatrate/active', 1),
        ConfigFixture('carriers/freeshipping/active', 1),
        ConfigFixture('currency/options/allow', 'USD'),
        ConfigFixture('currency/options/base', 'USD'),
        ConfigFixture('currency/options/default', 'USD'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, [
            'currency' => 'USD'
        ], 'cart'),
        DataFixture(QuoteIdMask::class, [
            'cart_id' => '$cart.id$'
        ], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ])
    ]
    public function testEstimatedShippingMethodForGuest()
    {
        $guestCart = DataFixtureStorageManager::getStorage()->get('cart');
        $currencyCode = $guestCart->getCurrency()->getQuoteCurrencyCode();
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();

        $query = <<<QUERY
        mutation {
            estimateShippingMethods(input:{
              cart_id: "{$maskedQuoteId}"
              address: {
                country_code: US
              }
            })
            {
              amount{
                currency
                value
              }
              available
              carrier_code
              price_excl_tax {
                currency
                value
              }
            }
          }
QUERY;
        $response = $this->graphQlMutation($query);
        self::assertEquals(
            $this->getExpectedQueryResponseForGuest($currencyCode),
            $response['estimateShippingMethods']
        );
    }

    #[
        ConfigFixture('shipping/origin/country_id', 'US'),
        ConfigFixture('general/store_information/country_id', 'US'),
        ConfigFixture('carriers/flatrate/active', 1),
        ConfigFixture('carriers/tablerate/active', 1),
        ConfigFixture('carriers/tablerate/condition_name', 'package_qty'),
        ConfigFixture('carriers/freeshipping/active', 1),
        ConfigFixture('currency/options/allow', 'USD,EUR'),
        ConfigFixture('currency/options/base', 'USD'),
        ConfigFixture('currency/options/default', 'USD'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, [
            'currency' => 'USD'
        ], 'cart'),
        DataFixture(QuoteIdMask::class, [
            'cart_id' => '$cart.id$'
        ], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ]),
        DataFixture(TablerateFixture::class, [
            'dest_country_id' => 'US',
            'dest_region_id' => 1,
            'condition_name' => 'package_qty',
            'condition_value' => 1,
            'price' => 35,
            'cost' => 0
        ], 'tablerate1'),
        DataFixture(TablerateFixture::class, [
            'dest_country_id' => 'US',
            'dest_region_id' => 2,
            'condition_name' => 'package_qty',
            'condition_value' => 1,
            'price' => 55,
            'cost' => 0
        ], 'tablerate2')
    ]
    public function testEstimatedShippingMethodTablerateForGuest()
    {
        $guestCart = DataFixtureStorageManager::getStorage()->get('cart');
        $currencyCode = $guestCart->getCurrency()->getQuoteCurrencyCode();
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();

        $query = <<<QUERY
        mutation {
            estimateShippingMethods(input:{
              cart_id: "{$maskedQuoteId}"
              address: {
                country_code: US
                region: {
                    region_id: 2
                }
                }
            })
            {
              amount{
                currency
                value
              }
              available
              carrier_code
              price_excl_tax {
                currency
                value
              }
            }
          }
QUERY;
        $response = $this->graphQlMutation($query);
        self::assertEquals(
            $this->getExpectedTablerateQueryResponseForGuest($currencyCode),
            $response['estimateShippingMethods']
        );
    }

    #[
        ConfigFixture('shipping/origin/country_id', 'US'),
        ConfigFixture('general/store_information/country_id', 'US'),
        ConfigFixture('carriers/flatrate/active', 0),
        ConfigFixture('carriers/tablerate/active', 1),
        ConfigFixture('carriers/tablerate/condition_name', 'package_qty'),
        ConfigFixture('carriers/freeshipping/active', 0),
        ConfigFixture('currency/options/allow', 'USD,EUR'),
        ConfigFixture('currency/options/base', 'USD'),
        ConfigFixture('currency/options/default', 'USD'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, [
            'customer_id' => '$customer.id$'
        ], as: 'cart'),
        DataFixture(QuoteIdMask::class, [
            'cart_id' => '$cart.id$'
        ], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ]),
        DataFixture(TablerateFixture::class, [
            'dest_country_id' => 'US',
            'dest_region_id' => 1,
            'condition_name' => 'package_qty',
            'condition_value' => 1,
            'price' => 35,
            'cost' => 0
        ], 'tablerate1'),
        DataFixture(TablerateFixture::class, [
            'dest_country_id' => 'US',
            'dest_region_id' => 2,
            'condition_name' => 'package_qty',
            'condition_value' => 1,
            'price' => 55,
            'cost' => 0
        ], 'tablerate2')
    ]
    public function testShippingMethodsTablerateEstimatedCostForLoggedInCustomer()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $currencyCode = $cart->getCurrency()->getQuoteCurrencyCode();
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();

        $query = <<<QUERY
        mutation {
            estimateShippingMethods(input:{
                cart_id: "{$maskedQuoteId}"
                address: {
                  country_code: US
                  region: {
                      region_id: 1
                  }
                  }
              })
            {
              amount{
                currency
                value
              }
              available
              carrier_code
              price_excl_tax {
                currency
                value
              }
            }
          }
QUERY;
        $response = $this->graphQlMutation($query);
        self::assertEquals(
            $this->getExpectedTablerateQueryResponseForLoggedInCustomer($currencyCode),
            $response['estimateShippingMethods']
        );
    }

    #[
        ConfigFixture('shipping/origin/country_id', 'US'),
        ConfigFixture('general/store_information/country_id', 'US'),
        ConfigFixture('carriers/flatrate/active', 1),
        ConfigFixture('carriers/freeshipping/active', 1),
        ConfigFixture('currency/options/allow', 'USD,EUR'),
        ConfigFixture('currency/options/base', 'USD'),
        ConfigFixture('currency/options/default', 'USD'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, [
            'customer_id' => '$customer.id$'
        ], as: 'cart'),
        DataFixture(QuoteIdMask::class, [
            'cart_id' => '$cart.id$'
        ], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ])
    ]
    public function testShippingMethodsEstimatedCostForLoggedInCustomer()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $currencyCode = $cart->getCurrency()->getQuoteCurrencyCode();
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();

        $query = <<<QUERY
        mutation {
            estimateShippingMethods(input:{
                cart_id: "{$maskedQuoteId}"
                address: {
                  country_code: US
                  }
              })
            {
              amount{
                currency
                value
              }
              available
              carrier_code
              price_excl_tax {
                currency
                value
              }
            }
          }
QUERY;
        $response = $this->graphQlMutation($query);
        self::assertEquals(
            $this->getExpectedQueryResponseForLoggedInCustomer($currencyCode),
            $response['estimateShippingMethods']
        );
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(QuoteIdMask::class, [
            'cart_id' => '$cart.id$'
        ], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ])
    ]
    public function testMissingRequiredCountyId()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();

        $query = <<<QUERY
mutation {
    estimateShippingMethods(input:{
        cart_id: "{$maskedQuoteId}"
        address: {
          postcode: "90210"
          }
      })
    {
      amount{
        currency
        value
      }
      available
      carrier_code
      price_excl_tax {
        currency
        value
      }
    }
  }
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Field EstimateAddressInput.country_code of required type CountryCodeEnum! was not provided.'
        );
        $this->graphQlMutation($query);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(QuoteIdMask::class, [
            'cart_id' => '$cart.id$'
        ], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ])
    ]
    public function testMissingRequiredCartId()
    {
        $query = <<<QUERY
mutation {
    estimateShippingMethods(input:{
        address: {
            country_code: US
        }
    })
    {
      amount{
        currency
        value
      }
      available
      carrier_code
      price_excl_tax {
        currency
        value
      }
    }
  }
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Field EstimateTotalsInput.cart_id of required type String! was not provided.'
        );
        $this->graphQlMutation($query);
    }

    /**
     * Returns response for estimated shipping methods for guest with tablerate
     *
     * @param string $currencyCode
     * @return array
     */
    private function getExpectedTablerateQueryResponseForGuest(string $currencyCode): array
    {
        return [
            0 =>
                [
                    'amount' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 0,
                        ],
                    'available' => true,
                    'carrier_code' => 'freeshipping',
                    'price_excl_tax' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 0,
                        ],
                ],
            1 =>
                [
                    'amount' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 5,
                        ],
                    'available' => true,
                    'carrier_code' => 'flatrate',
                    'price_excl_tax' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 5,
                        ],
                ],
            2 =>
                [
                    'amount' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 55,
                        ],
                    'available' => true,
                    'carrier_code' => 'tablerate',
                    'price_excl_tax' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 55,
                        ],
                ],
        ];
    }

    /**
     * Returns response for estimated shipping methods for guest (no tablerate)
     *
     * @param string $currencyCode
     * @return array
     */
    private function getExpectedQueryResponseForGuest(string $currencyCode): array
    {
        return [
            0 =>
                [
                    'amount' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 0,
                        ],
                    'available' => true,
                    'carrier_code' => 'freeshipping',
                    'price_excl_tax' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 0,
                        ],
                ],
            1 =>
                [
                    'amount' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 5,
                        ],
                    'available' => true,
                    'carrier_code' => 'flatrate',
                    'price_excl_tax' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 5,
                        ],
                ]
        ];
    }

    /**
     * Returns response for estimated shipping methods for logged in customer (no tablerate)
     *
     * @param string $currencyCode
     * @return array
     */
    private function getExpectedQueryResponseForLoggedInCustomer(string $currencyCode): array
    {
        return [
            0 =>
                [
                    'amount' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 0,
                        ],
                    'available' => true,
                    'carrier_code' => 'freeshipping',
                    'price_excl_tax' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 0,
                        ],
                ],
            1 =>
                [
                    'amount' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 5,
                        ],
                    'available' => true,
                    'carrier_code' => 'flatrate',
                    'price_excl_tax' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 5,
                        ],
                ]
        ];
    }

    /**
     * Returns response for estimated shipping methods with tablerate for logged in customer
     *
     * @param string $currencyCode
     * @return array
     */
    private function getExpectedTablerateQueryResponseForLoggedInCustomer(string $currencyCode): array
    {
        return [
            0 =>
                [
                    'amount' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 35,
                        ],
                    'available' => true,
                    'carrier_code' => 'tablerate',
                    'price_excl_tax' =>
                        [
                            'currency' => $currencyCode,
                            'value' => 35,
                        ],
                ],
        ];
    }
}
