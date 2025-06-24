<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Quote;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Subtotal;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\Shipping;
use Magento\Quote\Model\ShippingAssignment;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Combine as CombineCondition;
use Magento\SalesRule\Model\Rule\Condition\Product as ProductCondition;
use Magento\SalesRule\Test\Fixture\ProductCondition as ProductConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test discount totals calculation model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiscountTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var Discount
     */
    private $discountCollector;

    /**
     * @var Subtotal
     */
    private $subtotalCollector;

    /**
     * @var ShippingAssignment
     */
    private $shippingAssignment;

    /**
     * @var Shipping
     */
    private $shipping;

    /**
     * @var QuoteRepository
     */
    private $quote;

    /**
     * @var Total
     */
    private $total;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->criteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->quoteRepository = $this->objectManager->create(CartRepositoryInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->discountCollector = $this->objectManager->create(Discount::class);
        $this->subtotalCollector = $this->objectManager->create(Subtotal::class);
        $this->shippingAssignment = $this->objectManager->create(ShippingAssignment::class);
        $this->shipping = $this->objectManager->create(Shipping::class);
        $this->quote = $this->objectManager->get(QuoteRepository::class);
        $this->total = $this->objectManager->create(Total::class);
    }

    #[
        DataProvider('bundleProductWithDynamicPriceAndCartPriceRuleDataProvider'),
        AppIsolation(true),
        DataFixture(ProductFixture::class, ['price' => 10, 'special_price' => 5.99], as: 'simple1'),
        DataFixture(ProductFixture::class, ['price' => 20, 'special_price' => 15.99], as: 'simple2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$simple1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$simple2$']], 'opt2'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$', '$opt2$']], 'bundle'),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => '$bundle.sku$'],
            'cond1'
        ),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => '$simple1.sku$'],
            'cond2'
        ),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => '$simple2.sku$'],
            'cond3'
        ),
        DataFixture(
            RuleFixture::class,
            ['coupon_code' => 'bundle_cc', 'discount_amount' => 50, 'actions' => ['$cond1$']],
            'rule1'
        ),
        DataFixture(
            RuleFixture::class,
            ['coupon_code' => 'simple1_cc', 'discount_amount' => 50, 'actions' => ['$cond2$']],
            'rule2'
        ),
        DataFixture(
            RuleFixture::class,
            ['coupon_code' => 'simple2_cc', 'discount_amount' => 50, 'actions' => ['$cond3$']],
            'rule3'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle.id$',
                'selections' => [['$simple1.id$'], ['$simple2.id$']],
                'qty' => 1
            ],
        )
    ]
    public function testBundleProductWithDynamicPriceAndCartPriceRule(
        string $coupon,
        array $discounts,
        float $totalDiscount
    ): void {
        $quote = $this->quoteRepository->get($this->fixtures->get('cart')->getId());
        $quote->setCouponCode($coupon);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $this->assertEquals(21.98, $quote->getBaseSubtotal());
        $this->assertEquals($totalDiscount, $quote->getShippingAddress()->getDiscountAmount());
        $actual = [];
        $fixtures = [];
        $items = $quote->getAllItems();
        $this->assertCount(3, $items);
        /** @var Item $item*/
        foreach (array_keys($discounts) as $fixture) {
            $fixtures[$this->fixtures->get($fixture)->getId()] = $fixture;
        }
        foreach ($quote->getAllItems() as $item) {
            $actual[$fixtures[$item->getProductId()]] = $item->getDiscountAmount();
        }
        $this->assertEquals($discounts, $actual);
    }

    #[
        AppIsolation(true),
        DataFixture(ProductFixture::class, ['price' => 10, 'special_price' => 5.99], as: 'simple1'),
        DataFixture(ProductFixture::class, ['price' => 20, 'special_price' => 15.99], as: 'simple2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$simple1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$simple2$']], 'opt2'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$', '$opt2$']], 'bundle'),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => '$bundle.sku$'],
            'cond1'
        ),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => '$simple1.sku$'],
            'cond2'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::BY_PERCENT_ACTION,
                'discount_amount' => 20,
                'actions' => ['$cond1$'],
                'stop_rules_processing' => false,
                'sort_order' => 1,
            ],
            'rule1'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::BY_FIXED_ACTION,
                'discount_amount' => 1.5,
                'actions' => ['$cond2$'],
                'stop_rules_processing' => false,
                'sort_order' => 2,
            ],
            'rule2'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle.id$',
                'selections' => [['$simple1.id$'], ['$simple2.id$']],
                'qty' => 1
            ],
        )
    ]
    public function testBundleProductDynamicPriceWithBundleDiscountAndChildDiscount(): void
    {
        $discounts = [
            // bundle with dynamic price does not have discount on its own, instead it's distributed to children
            'bundle' => 0,
            // rule1 = (20/100 * 21.98) * (5.99 / 21.98) = 1.198
            // rule2 = 1.50
            // D = rule1 + rule1 = 1.198 + 1.50 = 2.698 ~ 2.70
            'simple1' => 2.70,
            // rule1 = (20/100 * 21.98) * (15.99 / 21.98) = 3.198
            // D = rule1 = 3.198 ~ 3.20
            'simple2' => 3.20,
        ];
        $quote = $this->quoteRepository->get($this->fixtures->get('cart')->getId());
        $this->assertEquals(21.98, $quote->getBaseSubtotal());
        $this->assertEquals(-5.90, $quote->getShippingAddress()->getDiscountAmount());
        $actual = [];
        $fixtures = [];
        $items = $quote->getAllItems();
        $this->assertCount(3, $items);
        /** @var Item $item*/
        foreach (array_keys($discounts) as $fixture) {
            $fixtures[$this->fixtures->get($fixture)->getId()] = $fixture;
        }
        foreach ($quote->getAllItems() as $item) {
            $actual[$fixtures[$item->getProductId()]] = $item->getDiscountAmount();
        }
        $this->assertEquals($discounts, $actual);
    }

    #[
        AppIsolation(true),
        DataFixture(ProductFixture::class, ['price' => 10, 'special_price' => 5.99], as: 'simple1'),
        DataFixture(ProductFixture::class, ['price' => 20, 'special_price' => 15.99], as: 'simple2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$simple1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$simple2$']], 'opt2'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$', '$opt2$']], 'bundle'),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => '$bundle.sku$'],
            'cond1'
        ),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => '$simple1.sku$'],
            'cond2'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::BY_PERCENT_ACTION,
                'discount_amount' => 20,
                'actions' => ['$cond1$'],
                'stop_rules_processing' => false,
                'sort_order' => 2,
            ],
            'rule1'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::BY_FIXED_ACTION,
                'discount_amount' => 1.5,
                'actions' => ['$cond2$'],
                'stop_rules_processing' => false,
                'sort_order' => 1,
            ],
            'rule2'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle.id$',
                'selections' => [['$simple1.id$'], ['$simple2.id$']],
                'qty' => 1
            ],
        )
    ]
    public function testBundleProductDynamicPriceWithChildDiscountAndBundleDiscount(): void
    {
        $discounts = [
            // bundle with dynamic price does not have discount on its own, instead it's distributed to children
            'bundle' => 0,
            // rule2 = 1.50
            // rule1 = (20/100 * (21.98 - 1.50) * ((5.99 - 1.50) / (21.98 - 1.50)) = 0.898
            // D = rule2 + rule1 = 1.50 + 0.898 = 2.398 ~ 2.40
            'simple1' => 2.40,
            // rule1 = (20/100 * (21.98 - 1.50) * (15.99 / (21.98 - 1.50)) = 3.198
            // D = rule1 = 3.198 ~ 3.20
            'simple2' => 3.20,
        ];
        $quote = $this->quoteRepository->get($this->fixtures->get('cart')->getId());
        $this->assertEquals(21.98, $quote->getBaseSubtotal());
        $this->assertEquals(-5.60, $quote->getShippingAddress()->getDiscountAmount());
        $actual = [];
        $fixtures = [];
        $items = $quote->getAllItems();
        $this->assertCount(3, $items);
        /** @var Item $item*/
        foreach (array_keys($discounts) as $fixture) {
            $fixtures[$this->fixtures->get($fixture)->getId()] = $fixture;
        }
        foreach ($quote->getAllItems() as $item) {
            $actual[$fixtures[$item->getProductId()]] = $item->getDiscountAmount();
        }
        $this->assertEquals($discounts, $actual);
    }

    /**
     * @return array
     */
    public static function bundleProductWithDynamicPriceAndCartPriceRuleDataProvider(): array
    {
        return [
            [
                'bundle_cc',
                [
                    // bundle with dynamic price does not have discount on its own, instead it's distributed to children
                    'bundle' => 0,
                    'simple1' => 3,
                    'simple2' => 7.99,
                ],
                -10.99
            ],
            [
                'simple1_cc',
                [
                    'bundle' => 0,
                    'simple1' => 3,
                    'simple2' => 0,
                ],
                -3
            ],
            [
                'simple2_cc',
                [
                    'bundle' => 0,
                    'simple1' => 0,
                    'simple2' => 8,
                ],
                -8
            ]
        ];
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    #[
        DataFixture(CategoryFixture::class, as: 'c1'),
        DataFixture(CategoryFixture::class, as: 'c2'),
        DataFixture(CategoryFixture::class, as: 'c3'),
        DataFixture(ProductFixture::class, [
            'price' => 40,
            'sku' => 'p1',
            'category_ids' => ['$c1.id$']
        ], 'p1'),
        DataFixture(ProductFixture::class, [
            'price' => 30,
            'sku' => 'p2',
            'category_ids' => ['$c1.id$', '$c2.id$']
        ], 'p2'),
        DataFixture(ProductFixture::class, [
            'price' => 20,
            'sku' => 'p3',
            'category_ids' => ['$c2.id$', '$c3.id$']
        ], 'p3'),
        DataFixture(ProductFixture::class, [
            'price' => 10,
            'sku' => 'p4',
            'category_ids' => ['$c3.id$']
        ], 'p4'),

        DataFixture(
            ProductConditionFixture::class,
            [
                'attribute' => 'category_ids',
                'value' => '$c1.id$',
                'operator' => '==',
                'conditions' => [
                    '1' => [
                        'type' => CombineCondition::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => [
                        'type' => ProductCondition::class,
                        'attribute' => 'category_ids',
                        'operator' => '==',
                        'value' => '$c1.id$',
                    ]
                ],
            ],
            'cond1'
        ),
        DataFixture(
            ProductConditionFixture::class,
            [
                'attribute' => 'category_ids',
                'value' => '$c2.id$',
                'operator' => '==',
                'conditions' => [
                    '1' => [
                        'type' => CombineCondition::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => [
                        'type' => ProductCondition::class,
                        'attribute' => 'category_ids',
                        'operator' => '==',
                        'value' => '$c2.id$',
                    ]
                ],
            ],
            'cond2'
        ),
        DataFixture(
            ProductConditionFixture::class,
            [
                'attribute' => 'category_ids',
                'value' => '$c3.id$',
                'operator' => '==',
                'conditions' => [
                    '1' => [
                        'type' => CombineCondition::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => [
                        'type' => ProductCondition::class,
                        'attribute' => 'category_ids',
                        'operator' => '==',
                        'value' => '$c3.id$',
                    ]
                ],
            ],
            'cond3'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'stop_rules_processing'=> 0,
                'coupon_code' => 'test',
                'discount_amount' => 10,
                'actions' => ['$cond1$'],
                'simple_action' => Rule::BY_FIXED_ACTION,
                'sort_order' => 0
            ],
            'rule1'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'discount_amount' => 5,
                'actions' => ['$cond2$'],
                'simple_action' => Rule::BY_FIXED_ACTION,
                'sort_order' => 1
            ],
            'rule2'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'stop_rules_processing'=> 0,
                'discount_amount' => 2,
                'actions' => ['$cond3$'],
                'simple_action' => Rule::BY_FIXED_ACTION,
                'sort_order' => 2
            ],
            'rule3'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p2.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p3.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p4.id$'])
    ]
    public function testDiscountOnSimpleProductWithDiscardSubsequentRule(): void
    {
        $cartId = (int)$this->fixtures->get('cart')->getId();
        $rule1Id = (int)$this->fixtures->get('rule1')->getId();
        $rule2Id = (int)$this->fixtures->get('rule2')->getId();
        $rule3Id = (int)$this->fixtures->get('rule3')->getId();
        $product1Id = (int) $this->fixtures->get('p1')->getId();
        $product2Id = (int) $this->fixtures->get('p2')->getId();
        $product3Id = (int) $this->fixtures->get('p3')->getId();
        $product4Id = (int) $this->fixtures->get('p4')->getId();
        $quote = $this->quote->get($cartId);
        $quote->setStoreId(1)->setIsActive(true)->setIsMultiShipping(0)->setCouponCode('test');
        $address = $quote->getShippingAddress();
        $this->shipping->setAddress($address);
        $this->shippingAssignment->setShipping($this->shipping);
        $this->shippingAssignment->setItems($address->getAllItems());
        $this->subtotalCollector->collect($quote, $this->shippingAssignment, $this->total);
        $this->discountCollector->collect($quote, $this->shippingAssignment, $this->total);
        $this->assertEquals(-32, $this->total->getDiscountAmount());
        $items = [];
        foreach ($quote->getAllItems() as $item) {
            $items[$item->getProductId()] = $item;
        }
        $this->assertEqualsCanonicalizing([$rule1Id,$rule2Id,$rule3Id], explode(',', $quote->getAppliedRuleIds()));
        $this->assertEqualsCanonicalizing([$rule1Id,$rule2Id,$rule3Id], explode(',', $address->getAppliedRuleIds()));
        $this->assertEqualsCanonicalizing([$rule1Id], explode(',', $items[$product1Id]->getAppliedRuleIds()));
        $this->assertEqualsCanonicalizing([$rule1Id,$rule2Id], explode(',', $items[$product2Id]->getAppliedRuleIds()));
        $this->assertEqualsCanonicalizing([$rule2Id], explode(',', $items[$product3Id]->getAppliedRuleIds()));
        $this->assertEqualsCanonicalizing([$rule3Id], explode(',', $items[$product4Id]->getAppliedRuleIds()));
    }

    #[
        AppIsolation(true),
        DataFixture(AttributeFixture::class, ['options' => [['label' => 'option1', 'sort_order' => 0]]], as: 'attr'),
        DataFixture(ProductFixture::class, ['price' => 100], as: 'p1'),
        DataFixture(ConfigurableProductFixture::class, ['_options' => ['$attr$'], '_links' => ['$p1$']], 'cp1'),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => '$p1.sku$'],
            'cond1'
        ),
        DataFixture(
            RuleFixture::class,
            ['simple_action' => Rule::CART_FIXED_ACTION, 'discount_amount' => 50, 'actions' => ['$cond1$']],
            'rule1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$cp1.id$', 'child_product_id' => '$p1.id$', 'qty' => 1],
        )
    ]
    public function testFixedAmountWholeCartDiscountOnConfigurableProduct(): void
    {
        $quote = $this->fixtures->get('cart');
        $this->assertEquals(50, $quote->getGrandTotal());
        $this->assertEquals(50, $quote->getSubtotalWithDiscount());
        $this->assertEquals(100, $quote->getSubtotal());

        $quote->getAllItems();

        //emulate a plugin on afterGetPrice
        foreach ($quote->getAllItems() as $item) {
            /** @var $item \Magento\Quote\Model\Quote\Item */
            $item->setPrice(200);
        }

        $quote->collectTotals();

        $this->assertEquals(50, $quote->getGrandTotal());
        $this->assertEquals(50, $quote->getSubtotalWithDiscount());
        $this->assertEquals(100, $quote->getSubtotal());
    }

    #[
        AppIsolation(true),
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 10], as:'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple2', 'price' => 20], as:'p2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$', 'price' => 10, 'price_type' => 0], as:'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p2.sku$', 'price' => 25, 'price_type' => 1], as:'link2'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Multiselect Options', 'type' => 'multi',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle-product-multiselect-checkbox-options','price' => 50,'price_type' => 1,
                '_options' => ['$opt1$', '$opt2$']],
            as:'bp1'
        ),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => 'bundle-product-multiselect-checkbox-options'],
            as:'cond1'
        ),
        DataFixture(
            RuleFixture::class,
            ['simple_action' => Rule::CART_FIXED_ACTION, 'discount_amount' => 50, 'actions' => ['$cond1$']],
            as:'rule1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p1.id$', '$p2.id$']],
                'qty' => 1
            ],
        )
    ]
    public function testFixedAmountWholeCartDiscountOnBundleProduct(): void
    {
        $quote = $this->fixtures->get('cart');
        $this->assertEquals(32.5, $quote->getGrandTotal());
        $this->assertEquals(32.5, $quote->getSubtotalWithDiscount());
        $this->assertEquals(82.5, $quote->getSubtotal());
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    #[
        DataFixture(CategoryFixture::class, as: 'c1'),
        DataFixture(ProductFixture::class, [
            'price' => 123,
            'sku' => 'p1',
            'category_ids' => ['$c1.id$']
        ], 'p1'),
        DataFixture(
            RuleFixture::class,
            [
                'stop_rules_processing'=> 0,
                'discount_amount' => 10,
                'simple_action' => Rule::BY_FIXED_ACTION,
                'sort_order' => 0
            ],
            'rule1'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'stop_rules_processing'=> 0,
                'discount_amount' => 20,
                'simple_action' => Rule::BY_PERCENT_ACTION,
                'discount_step' => 3,
                'sort_order' => 2
            ],
            'rule2'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'discount_amount' => 3,
                'simple_action' => Rule::BUY_X_GET_Y_ACTION,
                'discount_step' => 5,
                'sort_order' => 4
            ],
            'rule3'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart1.id$', 'product_id' => '$p1.id$']),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart2.id$', 'product_id' => '$p1.id$', 'qty' => 3]),
        DataFixture(GuestCartFixture::class, as: 'cart3'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart3.id$', 'product_id' => '$p1.id$', 'qty' => 9]),
    ]
    public function testDiscountOnSimpleProductWhenFurtherRulesHaveDiscountQtyStepSpecified(): void
    {
        $cart1Id = (int)$this->fixtures->get('cart1')->getId();
        $cart2Id = (int)$this->fixtures->get('cart2')->getId();
        $cart3Id = (int)$this->fixtures->get('cart3')->getId();
        $quote1 = $this->quote->get($cart1Id);
        $quote2 = $this->quote->get($cart2Id);
        $quote3 = $this->quote->get($cart3Id);
        $rule1Id = (int)$this->fixtures->get('rule1')->getId();
        $rule2Id = (int)$this->fixtures->get('rule2')->getId();
        $rule3Id = (int)$this->fixtures->get('rule3')->getId();

        $quote1->setStoreId(1)->setIsActive(true);
        $address = $quote1->getShippingAddress();
        $this->shipping->setAddress($address);
        $this->shippingAssignment->setShipping($this->shipping);
        $this->shippingAssignment->setItems($address->getAllItems());

        $this->subtotalCollector->collect($quote1, $this->shippingAssignment, $this->total);
        $this->discountCollector->collect($quote1, $this->shippingAssignment, $this->total);

        $this->assertEquals(-10, $this->total->getDiscountAmount());
        $this->assertEqualsCanonicalizing([$rule1Id], explode(',', $quote1->getAppliedRuleIds()));

        $quote2->setStoreId(1)->setIsActive(true);
        $address = $quote2->getShippingAddress();
        $this->shipping->setAddress($address);
        $this->shippingAssignment->setShipping($this->shipping);
        $this->shippingAssignment->setItems($address->getAllItems());

        $this->subtotalCollector->collect($quote2, $this->shippingAssignment, $this->total);
        $this->discountCollector->collect($quote2, $this->shippingAssignment, $this->total);

        $this->assertEquals(-97.8, $this->total->getDiscountAmount());
        $this->assertEqualsCanonicalizing([$rule1Id,$rule2Id], explode(',', $quote2->getAppliedRuleIds()));

        $quote3->setStoreId(1)->setIsActive(true);
        $address = $quote3->getShippingAddress();
        $this->shipping->setAddress($address);
        $this->shippingAssignment->setShipping($this->shipping);
        $this->shippingAssignment->setItems($address->getAllItems());

        $this->subtotalCollector->collect($quote3, $this->shippingAssignment, $this->total);
        $this->discountCollector->collect($quote3, $this->shippingAssignment, $this->total);

        $this->assertEquals(-662.4, $this->total->getDiscountAmount());
        $this->assertEqualsCanonicalizing([$rule1Id,$rule2Id,$rule3Id], explode(',', $quote3->getAppliedRuleIds()));
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    #[
        DataFixture(CategoryFixture::class, as: 'c1'),
        DataFixture(ProductFixture::class, [
            'price' => 123,
            'sku' => 'p1',
            'category_ids' => ['$c1.id$']
        ], 'p1'),
        DataFixture(
            RuleFixture::class,
            [
                'stop_rules_processing'=> 0,
                'discount_amount' => 33,
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_step' => 3,
                'sort_order' => 0
            ],
            'rule1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart1.id$', 'product_id' => '$p1.id$']),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart2.id$', 'product_id' => '$p1.id$', 'qty' => 5]),
    ]
    public function testFixedAmountDiscountForWholeCartOnSimpleProductWhenStepQtyIsSpecified(): void
    {
        $cart1Id = (int)$this->fixtures->get('cart1')->getId();
        $cart2Id = (int)$this->fixtures->get('cart2')->getId();
        $quote1 = $this->quote->get($cart1Id);
        $quote2 = $this->quote->get($cart2Id);
        $rule1Id = (int)$this->fixtures->get('rule1')->getId();

        $quote1->setStoreId(1)->setIsActive(true);
        $address = $quote1->getShippingAddress();
        $this->shipping->setAddress($address);
        $this->shippingAssignment->setShipping($this->shipping);
        $this->shippingAssignment->setItems($address->getAllItems());

        $this->subtotalCollector->collect($quote1, $this->shippingAssignment, $this->total);
        $this->discountCollector->collect($quote1, $this->shippingAssignment, $this->total);

        $this->assertEquals(-33, $this->total->getDiscountAmount());
        $this->assertEqualsCanonicalizing([$rule1Id], explode(',', $quote1->getAppliedRuleIds()));

        $quote2->setStoreId(1)->setIsActive(true);
        $address = $quote2->getShippingAddress();
        $this->shipping->setAddress($address);
        $this->shippingAssignment->setShipping($this->shipping);
        $this->shippingAssignment->setItems($address->getAllItems());

        $this->subtotalCollector->collect($quote2, $this->shippingAssignment, $this->total);
        $this->discountCollector->collect($quote2, $this->shippingAssignment, $this->total);

        $this->assertEquals(-33, $this->total->getDiscountAmount());
        $this->assertEqualsCanonicalizing([$rule1Id], explode(',', $quote2->getAppliedRuleIds()));
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    #[
        DataFixture(CategoryFixture::class, as: 'c1'),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 123,
                'sku' => 'p1',
                'category_ids' => ['$c1.id$']
            ],
            'p1'
        ),
        DataFixture(
            RuleFixture::class,
            [
                'discount_amount' => 1,
                'simple_action' => Rule::BUY_X_GET_Y_ACTION,
                'discount_step' => 3,
            ],
            'rule1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart1.id$', 'product_id' => '$p1.id$', 'qty' => 3]),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart2.id$', 'product_id' => '$p1.id$', 'qty' => 4]),
    ]
    public function testDiscountOnSimpleProductWhenBuyXGetYRuleHasDiscountQtyStepSpecified(): void
    {
        $cart1Id = (int)$this->fixtures->get('cart1')->getId();
        $quote1 = $this->quote->get($cart1Id);
        $rule1Id = (int)$this->fixtures->get('rule1')->getId();

        $quote1->setStoreId(1)->setIsActive(true);
        $address = $quote1->getShippingAddress();
        $this->shipping->setAddress($address);
        $this->shippingAssignment->setShipping($this->shipping);
        $this->shippingAssignment->setItems($address->getAllItems());

        $this->subtotalCollector->collect($quote1, $this->shippingAssignment, $this->total);
        $this->discountCollector->collect($quote1, $this->shippingAssignment, $this->total);

        $this->assertEquals(0, $this->total->getDiscountAmount());
        $this->assertNull($quote1->getAppliedRuleIds());

        $quote1->addProduct($this->fixtures->get('p1'), 1);

        $this->subtotalCollector->collect($quote1, $this->shippingAssignment, $this->total);
        $this->discountCollector->collect($quote1, $this->shippingAssignment, $this->total);

        $this->assertEquals(-123, $this->total->getDiscountAmount());
        $this->assertEqualsCanonicalizing([$rule1Id], explode(',', $quote1->getAppliedRuleIds()));

        $quote1->setItemQty($this->fixtures->get('p1')->getId(), 3);

        $this->subtotalCollector->collect($quote1, $this->shippingAssignment, $this->total);
        $this->discountCollector->collect($quote1, $this->shippingAssignment, $this->total);

        $this->assertEquals(-123, $this->total->getDiscountAmount());
        $this->assertEqualsCanonicalizing([$rule1Id], explode(',', $quote1->getAppliedRuleIds()));
    }
}
