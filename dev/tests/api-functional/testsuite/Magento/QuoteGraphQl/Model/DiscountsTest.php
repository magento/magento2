<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\Virtual as VirtualProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class DiscountsTest extends GraphQlAbstract
{
    /** @var CustomerTokenServiceInterface */
    private CustomerTokenServiceInterface $customerTokenService;

    /** @var Rule|null */
    protected ?Rule $createdRule = null;

    /** @inheritdoc */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        parent::setUp();
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(SalesRuleFixture::class, ['discount_amount' => 10, 'simple_action' => 'by_percent'], as: 'rule')
    ]
    /**
     * Test discounts resolver for a non-virtual quote
     * @throws LocalizedException
     */
    public function testDiscountsNonVirtualQuote()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getCartDiscountsQuery($maskedQuoteId);

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getHeaderMap()
        );
        $cartData = $response['cart'];
        $discounts = $cartData['prices']['discounts'] ?? [];
        $this->assertNotEmpty($discounts);
        $this->assertCount(1, $discounts);
        $discount = $discounts[0];
        $this->assertArrayHasKey('label', $discount);
        $this->assertArrayHasKey('amount', $discount);
        $this->assertArrayHasKey('value', $discount['amount']);
        $this->assertArrayHasKey('currency', $discount['amount']);
        $this->assertArrayHasKey('applied_to', $discount);
        $this->assertEquals('Discount', $discount['label']);
        $this->assertGreaterThan(0, $discount['amount']['value']);
        $this->assertEquals('USD', $discount['amount']['currency']);
        $this->assertEquals('ITEM', $discount['applied_to']);
    }

    #[
        DataFixture(
            VirtualProductFixture::class,
            ['sku' => 'virtual111', 'price' => 100, 'category_ids' => [2]],
            as: 'product1'
        ),
        DataFixture(
            VirtualProductFixture::class,
            ['sku' => 'virtual222', 'price' => 100, 'category_ids' => [2]],
            as: 'product2'
        ),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product1.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product2.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask')
    ]
    /**
     * Test discounts resolver for a virtual quote with conditional discount on specific SKU
     *
     * @throws Exception
     */
    public function testDiscountsVirtualQuote()
    {
        $this->createSalesRuleForSku("virtual222");
        $quoteIdMask = DataFixtureStorageManager::getStorage()->get('quoteIdMask');
        $maskedQuoteId = $quoteIdMask->getMaskedId();
        $query = $this->getCartDiscountsQueryWithItems($maskedQuoteId);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getHeaderMap()
        );
        $cartData = $response['cart'];
        $this->assertCount(2, $cartData['items']);
        $virtual1Item = null;
        $virtual2Item = null;
        foreach ($cartData['items'] as $item) {
            if ($item['product']['sku'] === 'virtual111') {
                $virtual1Item = $item;
            } elseif ($item['product']['sku'] === 'virtual222') {
                $virtual2Item = $item;
            }
        }

        $this->assertNotNull($virtual1Item, 'virtual1 item not found in cart');
        $this->assertNotNull($virtual2Item, 'virtual2 item not found in cart');
        $this->assertEmpty($virtual1Item['prices']['discounts'], 'virtual111 should not have any discounts');
        $this->assertNotEmpty($virtual2Item['prices']['discounts'], 'virtual222 should have discounts');
        $this->assertCount(1, $virtual2Item['prices']['discounts']);
        $discount = $virtual2Item['prices']['discounts'][0];
        $this->assertArrayHasKey('label', $discount);
        $this->assertArrayHasKey('amount', $discount);
        $this->assertArrayHasKey('value', $discount['amount']);
        $this->assertEquals('Discount', $discount['label']);
        $this->assertEquals(10, $discount['amount']['value']);
        $cartDiscounts = $cartData['prices']['discounts'] ?? [];
        $this->assertNotEmpty($cartDiscounts, 'Cart should have discounts');
        $this->assertCount(1, $cartDiscounts);
        $this->assertEquals('Discount', $cartDiscounts[0]['label']);
        $this->assertEquals('ITEM', $cartDiscounts[0]['applied_to']);
        $this->assertEquals(10, $cartDiscounts[0]['amount']['value']);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask')
    ]
    /**
     * Test discounts resolver when no discounts are applied
     */
    public function testDiscountsNoDiscounts()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getCartDiscountsQuery($maskedQuoteId);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getHeaderMap()
        );
        $cartData = $response['cart'];
        $discounts = $cartData['prices']['discounts'] ?? [];
        $this->assertEmpty($discounts);
    }

    /**
     * Get cart discounts query
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartDiscountsQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
        query {
            cart(cart_id: "{$maskedQuoteId}") {
                prices {
                    discounts {
                        label
                        amount {
                            value
                            currency
                        }
                        applied_to
                    }
                }
            }
        }
        QUERY;
    }

    /**
     * Get cart discounts query with items
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartDiscountsQueryWithItems(string $maskedQuoteId): string
    {
        return <<<QUERY
        query {
            cart(cart_id: "{$maskedQuoteId}") {
                items {
                    product {
                        sku
                        name
                    }
                    prices {
                        price {
                            value
                        }
                        row_total {
                            value
                        }
                        discounts {
                            label
                            amount {
                                value
                                currency
                            }
                        }
                    }
                }
                prices {
                    discounts {
                        label
                        amount {
                            value
                            currency
                        }
                        applied_to
                    }
                }
            }
        }
        QUERY;
    }

    /**
     * Get bearer authorization header
     *
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Create SalesRule with specific sku condition
     *
     * @param string $sku
     * @param int $discountPercent
     * @return Rule
     * @throws Exception
     */
    protected function createSalesRuleForSku(string $sku, int $discountPercent = 10): Rule
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Rule $rule */
        $rule = $objectManager->create(Rule::class);
        $rule->setName("{$discountPercent}% off for {$sku}")
            ->setIsActive(1)
            ->setSimpleAction('by_percent');
        $rule->loadPost([
            'name' => "{$discountPercent}% " . "off for virtual222",
            'is_active' => 1,
            'simple_action' => 'by_percent',
            'discount_amount' => $discountPercent,
            'website_ids' => [1],
            'customer_group_ids' => [0, 1, 2, 3],
            'actions' => [
                1 => [
                    'type' => Combine::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                    'actions' => [
                        1 => [
                            'type' => Product::class,
                            'attribute' => 'sku',
                            'operator' => '==',
                            'value' => 'virtual222',
                            'is_value_processed' => false,
                        ]
                    ]
                ]
            ],
        ]);
        $rule->save();
        $this->createdRule = $rule;
        return $rule;
    }

    protected function tearDown(): void
    {
        if ($this->createdRule && $this->createdRule->getId()) {
            $this->createdRule->delete();
        }
        parent::tearDown();
    }
}
