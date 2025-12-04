<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
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
use Magento\Quote\Test\Fixture\ApplyCoupon as ApplyCouponFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test getting estimateTotals schema with discounts fields for virtual products
 */
class EstimateTotalsWithVirtualProductDiscountsTest extends GraphQlAbstract
{
    private const PRODUCT_PRICE = 100;
    private const DISCOUNT_PERCENTAGE = 20;
    private const TOTAL_QTY = 1;
    private const DISCOUNT_LABEL = 'Flat 20 percent off';
    private const COUPON_CODE = 'SALE20';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test estimateTotals.discounts for virtual product
     *
     * @return void
     * @throws AuthenticationException|LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(
            AddressConditionFixture::class,
            [
                'attribute' => 'total_qty',
                'operator' => '>=',
                'value' => 1
            ],
            'condition'
        ),
        DataFixture(
            SalesRuleFixture::class,
            [
                'store_labels' => [1 => self::DISCOUNT_LABEL],
                'coupon_type' => SalesRule::COUPON_TYPE_SPECIFIC,
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'discount_amount' => self::DISCOUNT_PERCENTAGE,
                'coupon_code' => self::COUPON_CODE,
                'conditions' => ['$condition$'],
                'uses_per_customer' => 1,
                'stop_rules_processing' => true
            ],
            as: 'rule'
        ),
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE, 'type_id' => 'virtual'], as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$',
                'qty' => self::TOTAL_QTY
            ]
        ),
        DataFixture(
            ApplyCouponFixture::class,
            [
                'cart_id' => '$quote.id$',
                'coupon_codes' => [self::COUPON_CODE]
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testEstimateTotalsWithDiscount(): void
    {
        self::assertEquals(
            [
                'estimateTotals' => [
                    'cart' => [
                        'prices' => [
                            'discounts' => [
                                0 => [
                                    'amount' => [
                                        'value' => 20,
                                        'currency' => "USD"
                                    ],
                                    'label' => self::DISCOUNT_LABEL,
                                    'coupon' => [
                                        'code' => self::COUPON_CODE
                                    ],
                                    'applied_to' => "ITEM"
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlMutation(
                $this->getEstimateTotalsMutation($this->fixtures->get('quoteIdMask')->getMaskedId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Generates GraphQl mutation for estimateTotal with discounts
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getEstimateTotalsMutation(string $maskedQuoteId): string
    {
        return <<<MUTATION
            mutation {
                estimateTotals(
                    input: {
                        cart_id: "$maskedQuoteId"
                        address: {
                            country_code: US
                            postcode: "36104"
                            region: { region_code: "AL" }
                        }
                        shipping_method: {
                            carrier_code: "flatrate",
                            method_code: "flatrate"
                        }
                    }
                ) {
                    cart {
                        prices {
                            discounts {
                                amount {
                                    value
                                    currency
                                }
                                label
                                coupon {
                                    code
                                }
                                applied_to
                            }
                        }
                    }
                }
            }
        MUTATION;
    }

    /**
     * Returns the header with customer token for GQL Mutation
     *
     * @param string $email
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
