<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\AddressCondition as AddressConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\Tax\Test\Fixture\ProductTaxClass as ProductTaxClassFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for guestOrder.items.prices
 */
class OrderItemPricesTest extends GraphQlAbstract
{
    private const PRODUCT_PRICE = 25;
    private const PRODUCT_SPECIAL_PRICE = 20;
    private const TAX_PERCENTAGE = 10;
    private const DISCOUNT_PERCENTAGE = 10;
    private const TOTAL_QTY = 2;
    private const DISCOUNT_LABEL = 'COUPON_1';

    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Test order items prices
     *
     * @return void
     * @throws Exception
     */
    #[
        DataFixture(
            AddressConditionFixture::class,
            ['attribute' => 'total_qty', 'operator' => '>=', 'value' => 1],
            'condition'
        ),
        DataFixture(
            SalesRuleFixture::class,
            [
                'store_labels' => [1 => self::DISCOUNT_LABEL],
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'discount_amount' => self::DISCOUNT_PERCENTAGE,
                'conditions' => ['$condition$']
            ]
        ),
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, ['rate' => self::TAX_PERCENTAGE], 'rate'),
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
                'price' => self::PRODUCT_PRICE,
                'special_price' => self::PRODUCT_SPECIAL_PRICE,
                'custom_attributes' => ['tax_class_id' => '$product_tax_class.classId$']
            ],
            'product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => self::TOTAL_QTY]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testOrderItemPricesWithSpecialPriceAndTax(): void
    {
        $order = $this->fixtures->get('order');
        $response = $this->graphQlQuery(
            $this->getQuery(
                $order->getIncrementId(),
                $order->getBillingAddress()->getEmail(),
                $order->getBillingAddress()->getLastname()
            )
        );

        self::assertEquals(
            $this->getExpectedResponse(
                self::PRODUCT_SPECIAL_PRICE,
                22, //self::PRODUCT_SPECIAL_PRICE including 10% tax,
                44, //22 * 2
                27.5, //self::PRODUCT_PRICE including 10% tax
                55, //(self::PRODUCT_PRICE including 10% tax) * 2
                4 //10% of 40
            ),
            $response
        );
    }

    /**
     * Test order items prices without tax
     *
     * @return void
     * @throws Exception
     */
    #[
        DataFixture(
            AddressConditionFixture::class,
            ['attribute' => 'total_qty', 'operator' => '>=', 'value' => 1],
            'condition'
        ),
        DataFixture(
            SalesRuleFixture::class,
            [
                'store_labels' => [1 => self::DISCOUNT_LABEL],
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'discount_amount' => self::DISCOUNT_PERCENTAGE,
                'conditions' => ['$condition$']
            ]
        ),
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => self::TOTAL_QTY]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testOrderItemPricesWithoutSpecialPriceAndTax(): void
    {
        $order = $this->fixtures->get('order');
        $response = $this->graphQlQuery(
            $this->getQuery(
                $order->getIncrementId(),
                $order->getBillingAddress()->getEmail(),
                $order->getBillingAddress()->getLastname()
            )
        );

        self::assertEquals(
            $this->getExpectedResponse(
                self::PRODUCT_PRICE,
                self::PRODUCT_PRICE, //tax percentage is zero
                50, //self::PRODUCT_PRICE * 2
                self::PRODUCT_PRICE, //tax percentage is zero
                50, //tax percentage is zero
                5 //10% of 50
            ),
            $response
        );
    }

    /**
     * Generates GraphQl query for retrieving guest cart prices
     *
     * @param string $number
     * @param string $email
     * @param string $lastname
     * @return string
     */
    private function getQuery(string $number, string $email, string $lastname): string
    {
        return <<<QUERY
{
  guestOrder(input: {
      number: "{$number}",
      email: "{$email}",
      lastname: "{$lastname}"
  }) {
    items {
      prices {
        price {
          value
        }
        price_including_tax {
          value
        }
        row_total {
          value
        }
        row_total_including_tax {
          value
        }
        discounts {
          amount {
            value
          }
          label
        }
        total_item_discount {
          value
        }
        original_price {
          value
        }
        original_price_including_tax {
          value
        }
        original_row_total {
          value
        }
        original_row_total_including_tax {
          value
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Returns the expected result of cart query with items.prices
     *
     * @param $productPrice
     * @param $priceIncludingTax
     * @param $rowTotalIncludingTax
     * @param $originalPriceIncludingTax
     * @param $originalRowTotalInclTax
     * @return array[]
     */
    private function getExpectedResponse(
        $productPrice,
        $priceIncludingTax,
        $rowTotalIncludingTax,
        $originalPriceIncludingTax,
        $originalRowTotalInclTax,
        $discount
    ): array {
        return [
            'guestOrder' => [
                'items' => [
                    0 => [
                        'prices' => [
                            'price' => [
                                'value' => $productPrice
                            ],
                            'price_including_tax' => [
                                'value' => $priceIncludingTax
                            ],
                            'row_total' => [
                                'value' => $productPrice * self::TOTAL_QTY
                            ],
                            'row_total_including_tax' => [
                                'value' => $rowTotalIncludingTax
                            ],
                            'discounts' => [
                                0 => [
                                    'amount' => [
                                        'value' => $discount
                                    ],
                                    'label' => self::DISCOUNT_LABEL
                                ]
                            ],
                            'total_item_discount' => [
                                'value' => $discount
                            ],
                            'original_price' => [
                                'value' => self::PRODUCT_PRICE
                            ],
                            'original_price_including_tax' => [
                                'value' => $originalPriceIncludingTax
                            ],
                            'original_row_total' => [
                                'value' => self::PRODUCT_PRICE * self::TOTAL_QTY
                            ],
                            'original_row_total_including_tax' => [
                                'value' => $originalRowTotalInclTax
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
