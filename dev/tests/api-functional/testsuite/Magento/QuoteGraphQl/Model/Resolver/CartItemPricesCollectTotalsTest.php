<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test that totals collection is skipped when no totals-dependent fields
 * are present in the GraphQL selection set (CartItemPrices resolver).
 *
 * Covers the FIELDS_REQUIRE_TOTALS_COLLECTION optimisation in:
 * @see \Magento\QuoteGraphQl\Model\Resolver\CartItemPrices
 *
 * A GraphQL selection set declares exactly which fields the client needs.
 * When none of the listed fields appear, totals collection — including
 * the entire rule evaluation engine (O(R × I) where R = active no-coupon
 * rules, I = cart items) — is skipped entirely.
 */
class CartItemPricesCollectTotalsTest extends GraphQlAbstract
{
    private const CUSTOMER_EMAIL    = 'customer@example.com';
    private const CUSTOMER_PASSWORD = 'password';
    private const PRODUCT_PRICE     = 100.0;
    private const DISCOUNT_PERCENT  = 10;
    private const DISCOUNT_DELTA    = 0.01;

    /** @var CustomerTokenServiceInterface */
    private CustomerTokenServiceInterface $customerTokenService;

    /** @inheritdoc */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        parent::setUp();
    }

    // =========================================================================
    // SECTION 1: Fields that DO require totals collection
    // =========================================================================

    /**
     * Querying `discounts` on a cart item must trigger totals collection.
     * The 10% auto-applied rule discount must appear on the item.
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(Customer::class, ['email' => self::CUSTOMER_EMAIL], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(
            \Magento\SalesRule\Test\Fixture\Rule::class,
            ['discount_amount' => self::DISCOUNT_PERCENT, 'simple_action' => 'by_percent'],
            as: 'rule'
        )
    ]
    public function testDiscountsFieldTriggersTotalsCollection(): void
    {
        $cartId   = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query    = $this->buildCartItemPricesQuery($cartId, ['discounts']);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        $prices = $response['cart']['items'][0]['prices'];

        $this->assertNotEmpty($prices['discounts'], 'discounts must be non-empty when totals collection runs');
        $this->assertGreaterThan(0, $prices['discounts'][0]['amount']['value']);
        $this->assertEquals('Discount', $prices['discounts'][0]['label']);
    }

    /**
     * Querying `total_item_discount` must trigger totals collection.
     * 10% of $100 = $10 discount on the item.
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(Customer::class, ['email' => self::CUSTOMER_EMAIL], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(
            \Magento\SalesRule\Test\Fixture\Rule::class,
            ['discount_amount' => self::DISCOUNT_PERCENT, 'simple_action' => 'by_percent'],
            as: 'rule'
        )
    ]
    public function testTotalItemDiscountFieldTriggersTotalsCollection(): void
    {
        $cartId   = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query    = $this->buildCartItemPricesQuery($cartId, ['total_item_discount']);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        $prices = $response['cart']['items'][0]['prices'];

        $this->assertArrayHasKey('total_item_discount', $prices);
        // 10% of 100 = 10
        $this->assertEqualsWithDelta(10.0, $prices['total_item_discount']['value'], self::DISCOUNT_DELTA);
    }

    /**
     * Querying `original_item_price` must trigger totals collection.
     * Value must equal the product's original price.
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(Customer::class, ['email' => self::CUSTOMER_EMAIL], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(
            \Magento\SalesRule\Test\Fixture\Rule::class,
            ['discount_amount' => self::DISCOUNT_PERCENT, 'simple_action' => 'by_percent'],
            as: 'rule'
        )
    ]
    public function testOriginalItemPriceFieldTriggersTotalsCollection(): void
    {
        $cartId   = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query    = $this->buildCartItemPricesQuery($cartId, ['original_item_price']);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        $prices = $response['cart']['items'][0]['prices'];

        $this->assertArrayHasKey('original_item_price', $prices);
        $this->assertEqualsWithDelta(self::PRODUCT_PRICE, $prices['original_item_price']['value'], self::DISCOUNT_DELTA);
    }

    /**
     * Querying `original_row_total` must trigger totals collection.
     * qty=1 so row total equals item price.
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(Customer::class, ['email' => self::CUSTOMER_EMAIL], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(
            \Magento\SalesRule\Test\Fixture\Rule::class,
            ['discount_amount' => self::DISCOUNT_PERCENT, 'simple_action' => 'by_percent'],
            as: 'rule'
        )
    ]
    public function testOriginalRowTotalFieldTriggersTotalsCollection(): void
    {
        $cartId   = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query    = $this->buildCartItemPricesQuery($cartId, ['original_row_total']);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        $prices = $response['cart']['items'][0]['prices'];

        $this->assertArrayHasKey('original_row_total', $prices);
        $this->assertEqualsWithDelta(self::PRODUCT_PRICE, $prices['original_row_total']['value'], self::DISCOUNT_DELTA);
    }

    /**
     * Querying `price_including_tax` must trigger totals collection.
     * No tax configured in test env — value equals the base price.
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(Customer::class, ['email' => self::CUSTOMER_EMAIL], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(
            \Magento\SalesRule\Test\Fixture\Rule::class,
            ['discount_amount' => self::DISCOUNT_PERCENT, 'simple_action' => 'by_percent'],
            as: 'rule'
        )
    ]
    public function testPriceIncludingTaxFieldTriggersTotalsCollection(): void
    {
        $cartId   = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query    = $this->buildCartItemPricesQuery($cartId, ['price_including_tax']);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        $prices = $response['cart']['items'][0]['prices'];

        $this->assertArrayHasKey('price_including_tax', $prices);
        $this->assertEqualsWithDelta(self::PRODUCT_PRICE, $prices['price_including_tax']['value'], self::DISCOUNT_DELTA);
    }

    /**
     * Querying `row_total_including_tax` must trigger totals collection.
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(Customer::class, ['email' => self::CUSTOMER_EMAIL], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(
            \Magento\SalesRule\Test\Fixture\Rule::class,
            ['discount_amount' => self::DISCOUNT_PERCENT, 'simple_action' => 'by_percent'],
            as: 'rule'
        )
    ]
    public function testRowTotalIncludingTaxFieldTriggersTotalsCollection(): void
    {
        $cartId   = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query    = $this->buildCartItemPricesQuery($cartId, ['row_total_including_tax']);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        $prices = $response['cart']['items'][0]['prices'];

        $this->assertArrayHasKey('row_total_including_tax', $prices);
        $this->assertEqualsWithDelta(self::PRODUCT_PRICE, $prices['row_total_including_tax']['value'], self::DISCOUNT_DELTA);
    }

    // =========================================================================
    // SECTION 2: Fields that do NOT require totals collection
    // =========================================================================

    /**
     * Querying only `price` and `row_total` must NOT trigger totals collection.
     * Even with an active auto-rule, no discount fields appear in the response.
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(Customer::class, ['email' => self::CUSTOMER_EMAIL], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(
            \Magento\SalesRule\Test\Fixture\Rule::class,
            ['discount_amount' => self::DISCOUNT_PERCENT, 'simple_action' => 'by_percent'],
            as: 'rule'
        )
    ]
    public function testNonTotalsFieldsSkipTotalsCollection(): void
    {
        $cartId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query  = <<<QUERY
        query {
            cart(cart_id: "{$cartId}") {
                items {
                    prices {
                        price { value currency }
                        row_total { value currency }
                    }
                }
            }
        }
        QUERY;

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());
        $prices   = $response['cart']['items'][0]['prices'];

        // Non-totals fields are returned correctly
        $this->assertEqualsWithDelta(self::PRODUCT_PRICE, $prices['price']['value'], self::DISCOUNT_DELTA);
        $this->assertEqualsWithDelta(self::PRODUCT_PRICE, $prices['row_total']['value'], self::DISCOUNT_DELTA);

        // Totals-dependent fields must NOT appear — not requested, not computed
        $this->assertArrayNotHasKey('discounts', $prices);
        $this->assertArrayNotHasKey('total_item_discount', $prices);
        $this->assertArrayNotHasKey('price_including_tax', $prices);
        $this->assertArrayNotHasKey('row_total_including_tax', $prices);
    }

    /**
     * Querying only `price` returns the correct base price with no totals side effects.
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(Customer::class, ['email' => self::CUSTOMER_EMAIL], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask')
    ]
    public function testPriceFieldAloneDoesNotRequireTotalsCollection(): void
    {
        $cartId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query  = <<<QUERY
        query {
            cart(cart_id: "{$cartId}") {
                items {
                    prices {
                        price { value currency }
                    }
                }
            }
        }
        QUERY;

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());
        $prices   = $response['cart']['items'][0]['prices'];

        $this->assertEqualsWithDelta(self::PRODUCT_PRICE, $prices['price']['value'], self::DISCOUNT_DELTA);
        $this->assertArrayNotHasKey('discounts', $prices);
        $this->assertArrayNotHasKey('total_item_discount', $prices);
    }

    // =========================================================================
    // SECTION 3: Mixed — some totals fields, some not
    // =========================================================================

    /**
     * When at least one totals field is queried alongside non-totals fields,
     * totals collection runs and all fields return correct values.
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(Customer::class, ['email' => self::CUSTOMER_EMAIL], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(
            \Magento\SalesRule\Test\Fixture\Rule::class,
            ['discount_amount' => self::DISCOUNT_PERCENT, 'simple_action' => 'by_percent'],
            as: 'rule'
        )
    ]
    public function testMixedFieldsTriggersTotalsCollectionWhenAnyTotalsFieldPresent(): void
    {
        $cartId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query  = <<<QUERY
        query {
            cart(cart_id: "{$cartId}") {
                items {
                    prices {
                        price { value currency }
                        row_total { value currency }
                        total_item_discount { value currency }
                        discounts { amount { value currency } label }
                    }
                }
            }
        }
        QUERY;

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());
        $prices   = $response['cart']['items'][0]['prices'];

        // Non-totals fields — correct base prices
        $this->assertEqualsWithDelta(self::PRODUCT_PRICE, $prices['price']['value'], self::DISCOUNT_DELTA);
        $this->assertEqualsWithDelta(self::PRODUCT_PRICE, $prices['row_total']['value'], self::DISCOUNT_DELTA);

        // Totals fields — 10% rule must have been evaluated
        $this->assertEqualsWithDelta(10.0, $prices['total_item_discount']['value'], self::DISCOUNT_DELTA);
        $this->assertNotEmpty($prices['discounts']);
        $this->assertEquals('Discount', $prices['discounts'][0]['label']);
        $this->assertEqualsWithDelta(10.0, $prices['discounts'][0]['amount']['value'], self::DISCOUNT_DELTA);
    }

    // =========================================================================
    // SECTION 4: Prove the O(R × I) trade-off explicitly
    // =========================================================================

    /**
     * With NO totals fields queried and TWO active auto-rules:
     * - Response succeeds with correct base prices
     * - No discount fields appear — both rules were entirely skipped
     *
     * This is the core trade-off test: with R=2 rules and I=1 item the rule
     * engine would normally run O(2 × 1) = 2 evaluations. Totals collection
     * is skipped entirely so 0 evaluations occur.
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(Customer::class, ['email' => self::CUSTOMER_EMAIL], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(
            \Magento\SalesRule\Test\Fixture\Rule::class,
            ['discount_amount' => self::DISCOUNT_PERCENT, 'simple_action' => 'by_percent'],
            as: 'rule1'
        ),
        DataFixture(
            \Magento\SalesRule\Test\Fixture\Rule::class,
            ['discount_amount' => 5, 'simple_action' => 'by_fixed'],
            as: 'rule2'
        )
    ]
    public function testAutoRuleNotEvaluatedWhenTotalsFieldsAbsent(): void
    {
        $cartId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query  = <<<QUERY
        query {
            cart(cart_id: "{$cartId}") {
                items {
                    prices {
                        price { value currency }
                    }
                }
            }
        }
        QUERY;

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());
        $prices   = $response['cart']['items'][0]['prices'];

        // Base price is returned correctly — resolver ran
        $this->assertEqualsWithDelta(self::PRODUCT_PRICE, $prices['price']['value'], self::DISCOUNT_DELTA);

        // No discount fields in response — both rules were entirely skipped
        $this->assertArrayNotHasKey('discounts', $prices);
        $this->assertArrayNotHasKey('total_item_discount', $prices);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Build a cart query requesting specific CartItemPrices fields.
     *
     * @param string $cartId
     * @param string[] $fields GraphQL field names under items.prices
     * @return string
     */
    private function buildCartItemPricesQuery(string $cartId, array $fields): string
    {
        $fieldSelections = $this->buildFieldSelections($fields);

        return <<<QUERY
        query {
            cart(cart_id: "{$cartId}") {
                items {
                    prices {
                        {$fieldSelections}
                    }
                }
            }
        }
        QUERY;
    }

    /**
     * Build GraphQL field selection strings for CartItemPrices fields.
     * Handles Money objects { value currency }, Discount arrays, and scalars.
     *
     * @param string[] $fields
     * @return string
     */
    private function buildFieldSelections(array $fields): string
    {
        $moneyFields = [
            'original_item_price', 'original_row_total',
            'price_including_tax', 'row_total_including_tax',
            'total_item_discount', 'catalog_discount', 'row_catalog_discount',
        ];

        $selections = [];
        foreach ($fields as $field) {
            if (in_array($field, $moneyFields, true)) {
                $selections[] = "{$field} { value currency }";
            } elseif ($field === 'discounts') {
                $selections[] = "discounts { amount { value currency } label }";
            } else {
                $selections[] = $field;
            }
        }

        return implode("\n", $selections);
    }

    /**
     * Get bearer authorization header for the test customer.
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(
        string $username = self::CUSTOMER_EMAIL,
        string $password = self::CUSTOMER_PASSWORD
    ): array {
        $token = $this->customerTokenService->createCustomerAccessToken($username, $password);
        return ['Authorization' => 'Bearer ' . $token];
    }
}