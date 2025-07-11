<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Test\Fixture\ProductTaxClass;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Test\Fixture\AddProductToCart;

/**
 * Test for guest shipping methods estimate costs
 */
class EstimateTotalsTest extends GraphQlAbstract
{
    /**
     * @param string $countryCode
     * @param string $shipping
     * @param array $prices
     * @return void
     * @throws LocalizedException
     * @dataProvider estimationsProvider
     */
    #[
        DataFixture(
            ProductTaxClass::class,
            as: 'product_tax_class'
        ),
        DataFixture(
            TaxRateFixture::class,
            [
                'tax_country_id' => 'ES'
            ],
            'rate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$rate.id$']
            ],
            'rule'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'custom_attributes' => [
                    'tax_class_id' => '$product_tax_class.classId$'
                ],
            ],
            'product'
        ),
        DataFixture(GuestCart::class, ['currency' => 'USD'], 'cart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ])
    ]
    public function testEstimateTotals(string $countryCode, string $shipping, array $prices): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();

        $query = <<<QUERY
        mutation {
  estimateTotals(input: {
    cart_id: "{$maskedQuoteId}",
    address: {
      country_code: {$countryCode}
    },
    shipping_method: {
      carrier_code: "{$shipping}",
      method_code: "{$shipping}"
    }
  }) {
    cart {
      prices {
        grand_total {
          value
          currency
        }
        applied_taxes {
          amount {
            value
            currency
          }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query);

        self::assertEquals(
            [
                'estimateTotals' => [
                    'cart' => [
                        'prices' => $prices
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    #[
        DataFixture(
            ProductTaxClass::class,
            as: 'product_tax_class'
        ),
        DataFixture(
            TaxRateFixture::class,
            [
                'tax_country_id' => 'ES',
                'tax_postcode' => '08005',
            ],
            'rate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$rate.id$']
            ],
            'rule'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'custom_attributes' => [
                    'tax_class_id' => '$product_tax_class.classId$'
                ],
            ],
            'product'
        ),
        DataFixture(GuestCart::class, ['currency' => 'USD'], 'cart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ])
    ]
    public function testEstimateTotalsCleanPostCode(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();

        $query = <<<QUERY
        mutation {
  estimateTotals(input: {
    cart_id: "{$maskedQuoteId}",
    address: {
      country_code: ES
      postcode: "%s"
    },
    shipping_method: {
      carrier_code: "flatrate",
      method_code: "flatrate"
    }
  }) {
    cart {
      prices {
        applied_taxes {
          amount {
            value
            currency
          }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation(sprintf($query, '08005'));

        self::assertEquals(
            [
                'estimateTotals' => [
                    'cart' => [
                        'prices' => [
                            'applied_taxes' => [
                                [
                                    'amount' => [
                                        'value' => 1,
                                        'currency' => 'USD'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );

        $response = $this->graphQlMutation(sprintf($query, ''));

        self::assertEquals(
            [
                'estimateTotals' => [
                    'cart' => [
                        'prices' => [
                            'applied_taxes' => []
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public static function estimationsProvider(): array
    {
        return [
            [
                'ES',
                'freeshipping',
                [
                    'grand_total' => [
                        'value' => 11,
                        'currency' => 'USD'
                    ],
                    'applied_taxes' => [
                        [
                            'amount' => [
                                'value' => 1,
                                'currency' => 'USD'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'IE',
                'flatrate',
                [
                    'grand_total' => [
                        'value' => 15,
                        'currency' => 'USD'
                    ],
                    'applied_taxes' => []
                ]
            ]
        ];
    }
}
