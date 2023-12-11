<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Quote\Model\Quote;
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
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->discountCollector = $this->objectManager->create(Discount::class);
        $this->subtotalCollector = $this->objectManager->create(Subtotal::class);
        $this->shippingAssignment = $this->objectManager->create(ShippingAssignment::class);
        $this->shipping = $this->objectManager->create(Shipping::class);
        $this->quote = $this->objectManager->get(QuoteRepository::class);
        $this->total = $this->objectManager->create(Total::class);
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_bundle_product_with_dynamic_price.php
     * @dataProvider bundleProductWithDynamicPriceAndCartPriceRuleDataProvider
     * @param string $coupon
     * @param array $discounts
     * @param float $totalDiscount
     * @return void
     */
    #[
        AppIsolation(true),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => 'bundle_product_with_dynamic_price'],
            'cond1'
        ),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => 'simple1'],
            'cond2'
        ),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => 'simple2'],
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
    ]
    public function testBundleProductWithDynamicPriceAndCartPriceRule(
        string $coupon,
        array $discounts,
        float $totalDiscount
    ): void {
        $quote = $this->getQuote('quote_with_bundle_product_with_dynamic_price');
        $quote->setCouponCode($coupon);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $this->assertEquals(21.98, $quote->getBaseSubtotal());
        $this->assertEquals($totalDiscount, $quote->getShippingAddress()->getDiscountAmount());
        $items = $quote->getAllItems();
        $this->assertCount(3, $items);
        /** @var Item $item*/
        $item = array_shift($items);
        $this->assertEquals('bundle_product_with_dynamic_price-simple1-simple2', $item->getSku());
        $this->assertEquals($discounts[$item->getSku()], $item->getDiscountAmount());
        $item = array_shift($items);
        $this->assertEquals('simple1', $item->getSku());
        $this->assertEquals(5.99, $item->getPrice());
        $this->assertEquals($discounts[$item->getSku()], $item->getDiscountAmount());
        $item = array_shift($items);
        $this->assertEquals('simple2', $item->getSku());
        $this->assertEquals(15.99, $item->getPrice());
        $this->assertEquals($discounts[$item->getSku()], $item->getDiscountAmount());
    }

    /**
     * @return array
     */
    public function bundleProductWithDynamicPriceAndCartPriceRuleDataProvider(): array
    {
        return [
            [
                'bundle_cc',
                [
                    'bundle_product_with_dynamic_price-simple1-simple2' => 10.99,
                    'simple1' => 0,
                    'simple2' => 0,
                ],
                -10.99
            ],
            [
                'simple1_cc',
                [
                    'bundle_product_with_dynamic_price-simple1-simple2' => 0,
                    'simple1' => 3,
                    'simple2' => 0,
                ],
                -3
            ],
            [
                'simple2_cc',
                [
                    'bundle_product_with_dynamic_price-simple1-simple2' => 0,
                    'simple1' => 0,
                    'simple2' => 8,
                ],
                -8
            ]
        ];
    }

    /**
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        $searchCriteria = $this->criteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();
        $carts = $this->quoteRepository->getList($searchCriteria)
            ->getItems();
        return array_shift($carts);
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
}
