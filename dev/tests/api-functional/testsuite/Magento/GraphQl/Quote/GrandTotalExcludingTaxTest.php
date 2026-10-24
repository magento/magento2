<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\AddressCondition as AddressConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\Tax\Test\Fixture\ProductTaxClass as ProductTaxClassFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test getting cart grand_total_excluding_tax and grand_total
 */
class GrandTotalExcludingTaxTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Test grand_total_excluding_tax field is returning grand_total amount without tax
     * With discount applied
     *
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(
            AddressConditionFixture::class,
            ['attribute' => 'base_subtotal', 'operator' => '>=', 'value' => 0],
            'condition'
        ),
        DataFixture(
            SalesRuleFixture::class,
            [
                'store_labels' => [1 => 'Coupon1'],
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'uses_per_customer' => 1,
                'discount_amount' => 10,
                'stop_rules_processing' => false,
                'conditions' => ['$condition$']
            ]
        ),
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, as: 'rate'),
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
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]
        ),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testGetCartTotalsWithTaxAndDiscount()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertEquals(
            [
                'cart' => [
                    'prices' => [
                        'grand_total' => [
                            'value' => 19.8
                        ],
                        'grand_total_excluding_tax' => [
                            'value' => 18
                        ],
                        'applied_taxes' => [
                            [
                                'amount' => [
                                    'value' => 1.8
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * Test grand_total_excluding_tax field is returning grand_total amount without tax
     * With no discount applied
     *
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, as: 'rate'),
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
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]
        ),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testGetCartTotalsWithTaxAndNoDiscount()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertEquals(
            [
                'cart' => [
                    'prices' => [
                        'grand_total' => [
                            'value' => 22
                        ],
                        'grand_total_excluding_tax' => [
                            'value' => 20
                        ],
                        'applied_taxes' => [
                            [
                                'amount' => [
                                    'value' => 2
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * Test grand_total_excluding_tax field is returning grand_total amount without tax
     *
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, as: 'rate'),
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
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$', 'currency' => 'USD'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testAddShippingMethod(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getShippingMethodMutation($maskedQuoteId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertEquals(
            [
                'setShippingMethodsOnCart' => [
                    'cart' => [
                        'prices' => [
                            'grand_total' => [
                                'value' => 16,
                                'currency' => 'USD'
                            ],
                            'grand_total_excluding_tax' => [
                                'value' => 15,
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
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * Test grand_total_excluding_tax field is returning estimated grand_total amount without tax
     *
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, ['tax_country_id' => 'ES'], as: 'rate'),
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
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$', 'currency' => 'USD'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testEstimateTotals(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getEstimateTotalsMutation($maskedQuoteId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertEquals(
            [
                'estimateTotals' => [
                    'cart' => [
                        'prices' => [
                            'grand_total' => [
                                'value' => 15,
                                'currency' => 'USD'
                            ],
                            'grand_total_excluding_tax' => [
                                'value' => 15,
                                'currency' => 'USD'
                            ],
                            'applied_taxes' => []
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * Generates GraphQl query for retrieving cart grand totals
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    prices {
      grand_total {
        value
      }
      grand_total_excluding_tax {
        value
      }
      applied_taxes {
        amount {
          value
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Generates GraphQl mutation for retrieving estimateTotals
     *
     * @param $maskedQuoteId
     * @return string
     */
    private function getShippingMethodMutation($maskedQuoteId): string
    {
        return <<<QUERY
        mutation {
  setShippingMethodsOnCart(input: {
    cart_id: "{$maskedQuoteId}",
    shipping_methods: {
      carrier_code: "flatrate",
      method_code: "flatrate"
    }
  }) {
    cart {
      prices {
        grand_total {
          value
          currency
        }
        grand_total_excluding_tax {
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
    }

    /**
     * Generates GraphQl mutation for retrieving estimateTotals
     *
     * @param $maskedQuoteId
     * @return string
     */
    private function getEstimateTotalsMutation($maskedQuoteId): string
    {
        return <<<QUERY
        mutation {
  estimateTotals(input: {
    cart_id: "{$maskedQuoteId}",
    address: {
      country_code: IE
    },
    shipping_method: {
      carrier_code: "flatrate",
      method_code: "flatrate"
    }
  }) {
    cart {
      prices {
        grand_total {
          value
          currency
        }
        grand_total_excluding_tax {
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
    }

    /**
     * Generates token for GQL
     *
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
