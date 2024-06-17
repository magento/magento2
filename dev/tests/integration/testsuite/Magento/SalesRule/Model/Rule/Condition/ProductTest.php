<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Rule\Condition;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Magento\SalesRule\Model\Rule\Condition\Product\Subselect;
use Magento\SalesRule\Test\Fixture\ProductCondition as ProductConditionFixture;
use Magento\SalesRule\Test\Fixture\ProductFoundInCartConditions as ProductFoundInCartConditionsFixture;
use Magento\SalesRule\Test\Fixture\ProductSubselectionInCartConditions as ProductSubselectionInCartConditionsFixture;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    use ConditionHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\TestFramework\Fixture\DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var QuoteRepository
     */
    private $quote;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->quote = $this->objectManager->get(QuoteRepository::class);
    }

    /**
     * Ensure that SalesRules filtering on category validates children products of configurables
     *
     * 1. Load a quote with a configured product and a sales rule set to filter based on category
     * 2. Set product's associated category according to test case
     * 3. Attempt to validate the sales rule against the quote and assert the output is as expected
     *
     * @param int $categoryId
     * @param bool $expectedResult
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @dataProvider validateProductConditionDataProvider
     */
    #[
        AppIsolation(true),
        DbIsolation(false),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'category_ids', 'value' => '333'],
            'cond11'
        ),
        DataFixture(
            ProductFoundInCartConditionsFixture::class,
            ['conditions' => ['$cond11$']],
            'cond1'
        ),
        DataFixture(
            RuleFixture::class,
            ['discount_amount' => 50, 'conditions' => ['$cond1$']],
            'rule1'
        ),
    ]
    public function testValidateCategorySalesRuleIncludesChildren($categoryId, $expectedResult)
    {
        // Load the quote that contains a child of a configurable product
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class)
            ->load('test_cart_with_configurable', 'reserved_order_id');

        $ruleId = $this->fixtures->get('rule1')->getId();
        // Load the SalesRule looking for products in a specific category
        /** @var $rule Rule */
        $rule = $this->objectManager->create(Rule::class)->load($ruleId);

        // Prepare the parent product with the given category setting
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->get('configurable');
        $product->setCategoryIds([$categoryId]);
        $product->save();

        // Assert the validation result matches the expected result given the child product and category rule
        $this->assertEquals($expectedResult, $rule->validate($quote));
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Bundle/_files/order_item_with_bundle_and_options.php
     * @magentoDataFixture Magento/SalesRule/_files/rules_sku_exclude.php
     *
     * @return void
     */
    public function testValidateSalesRuleExcludesBundleChildren(): void
    {
        // Load the quote that contains a child of a bundle product
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class)
            ->load('test_cart_with_bundle_and_options', 'reserved_order_id');

        // Load the SalesRule looking for excluding products with selected sku
        /** @var $rule Rule */
        $rule = $this->objectManager->get(Registry::class)
            ->registry('_fixture/Magento_SalesRule_Sku_Exclude');

        $this->assertFalse($rule->validate($quote));
    }

    /**
     * @return array
     */
    public function validateProductConditionDataProvider()
    {
        $validCategoryId = 333;
        $invalidCategoryId = 2;
        return [
            [
                'categoryId' => $validCategoryId,
                'expectedResult' => true
            ],
            [
                'categoryId' => $invalidCategoryId,
                'expectedResult' => false
            ]
        ];
    }

    /**
     * Ensure that SalesRules filtering on quote items quantity validates configurable product correctly
     *
     * 1. Load a quote with a configured product and a sales rule set to filter items with quantity 2.
     * 2. Attempt to validate the sales rule against the quote and assert the output is negative.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     */
    #[
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'attribute_set_id', 'value' => '4'],
            'cond11'
        ),
        DataFixture(
            ProductSubselectionInCartConditionsFixture::class,
            ['attribute' => 'qty', 'operator' => '==', 'value' => 2, 'conditions' => ['$cond11$']],
            'cond1'
        ),
        DataFixture(
            RuleFixture::class,
            ['discount_amount' => 50, 'conditions' => ['$cond1$']],
            'rule1'
        ),
    ]
    public function testValidateQtySalesRuleWithConfigurable()
    {
        // Load the quote that contains a child of a configurable product with quantity 1.
        $quote = $this->getQuote('test_cart_with_configurable');

        $ruleId = $this->fixtures->get('rule1')->getId();
        // Load the SalesRule looking for products in a specific category
        /** @var $rule Rule */
        $rule = $this->objectManager->create(Rule::class)->load($ruleId);

        $this->assertFalse(
            $rule->validate($quote->getBillingAddress())
        );
    }

    /**
     * Ensure that SalesRules filtering on quote items quantity validates configurable product parent category correctly
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     * @magentoDataFixture Magento/SalesRule/_files/rules_parent_category.php
     * @dataProvider conditionsDataProvider
     */
    public function testValidateParentCategoryWithConfigurable(array $conditions, bool $expected): void
    {
        $quote = $this->getQuote('test_cart_with_configurable');
        $registry = $this->objectManager->get(Registry::class);
        /** @var Rule $rule */
        $rule = $this->objectManager->create(Rule::class);
        $ruleId = $registry->registry('50% Off on Configurable parent category');
        $rule->load($ruleId);
        $rule->getConditions()->setConditions([])->loadArray(
            [
                'type' => Combine::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'conditions' => $conditions
            ]
        );
        $rule->save();

        $this->assertEquals(
            $expected,
            $rule->validate($quote->getShippingAddress()),
            'Cart price rule validation failed.'
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function conditionsDataProvider(): array
    {
        return [
            'If total quantity  is 1 for a subselection of items in cart matching ALL of these conditions: ' .
            'Category (Parent Only) is not "Default Category"' => [
                'conditions' => [
                    [
                        'type' => Subselect::class,
                        'attribute' => 'qty',
                        'operator' => '==',
                        'value' => '1',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' => [
                                [
                                    'type' => Product::class,
                                    'attribute' => 'category_ids',
                                    'attribute_scope' => 'parent',
                                    'operator' => '!=',
                                    'value' => '2',
                                    'is_value_processed' => false,
                                ],
                            ],
                    ],
                ],
                'expected' => false
            ],
            'If total quantity  is 1 for a subselection of items in cart matching ALL of these conditions: ' .
            'Category (Parent Only) is "Default Category"' => [
                'conditions' => [
                    [
                        'type' => Subselect::class,
                        'attribute' => 'qty',
                        'operator' => '==',
                        'value' => '1',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' => [
                                [
                                    'type' => Product::class,
                                    'attribute' => 'category_ids',
                                    'attribute_scope' => 'parent',
                                    'operator' => '==',
                                    'value' => '2',
                                    'is_value_processed' => false,
                                ],
                            ],
                    ],
                ],
                'expected' => true
            ],
            'If an item is found in the cart with all these conditions true: ' .
            'Category (Parent Only) is not "Default Category"' => [
                'conditions' => [
                    [
                        'type' => Found::class,
                        'value' => '1',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' => [
                                [
                                    'type' => Product::class,
                                    'attribute' => 'category_ids',
                                    'attribute_scope' => 'parent',
                                    'operator' => '!=',
                                    'value' => '2',
                                    'is_value_processed' => false,
                                ],
                            ],
                    ],
                ],
                'expected' => false
            ],
            'If an item is found in the cart with all these conditions true: ' .
            'Category (Parent Only) is "Default Category"' => [
                'conditions' => [
                    [
                        'type' => Found::class,
                        'value' => '1',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' => [
                                [
                                    'type' => Product::class,
                                    'attribute' => 'category_ids',
                                    'attribute_scope' => 'parent',
                                    'operator' => '==',
                                    'value' => '2',
                                    'is_value_processed' => false,
                                ],
                            ],
                    ],
                ],
                'expected' => true
            ],
            'If an item is not found in the cart with all these conditions true: ' .
            'Category (Parent Only) is "Default Category"' => [
                'conditions' => [
                    [
                        'type' => Found::class,
                        'value' => '0',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' => [
                                [
                                    'type' => Product::class,
                                    'attribute' => 'category_ids',
                                    'attribute_scope' => 'parent',
                                    'operator' => '==',
                                    'value' => '2',
                                    'is_value_processed' => false,
                                ],
                            ],
                    ],
                ],
                'expected' => false
            ],
        ];
    }

    /**
     * Ensure that the coupon code shouldn't get applied as the cart contains products from restricted category
     *
     * @throws NoSuchEntityException
     * @return void
     */
    #[
        AppIsolation(true),
        DbIsolation(true),
        DataFixture(CategoryFixture::class, as: 'c1'),
        DataFixture(CategoryFixture::class, as: 'c2'),
        DataFixture(ProductFixture::class, [
            'price' => 40,
            'sku' => 'p1',
            'category_ids' => ['$c1.id$']
        ], 'p1'),
        DataFixture(ProductFixture::class, [
            'price' => 30,
            'sku' => 'p2',
            'category_ids' => ['$c2.id$']
        ], 'p2'),
        DataFixture(
            RuleFixture::class,
            [
                'stop_rules_processing'=> 0,
                'coupon_code' => 'test',
                'discount_amount' => 10,
                'conditions' => [
                    [
                        'type' => Combine::class,
                        'attribute' => null,
                        'operator' => null,
                        'value' => '1',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' => [
                            [
                                'type' => Found::class,
                                'value' => '0',
                                'is_value_processed' => null,
                                'aggregator' => 'all',
                                'conditions' => [
                                    [
                                        'type' => Product::class,
                                        'attribute' => 'category_ids',
                                        'operator' => '==',
                                        'value' => '$c1.id$',
                                        'is_value_processed' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'simple_action' => Rule::BY_FIXED_ACTION,
                'sort_order' => 0
            ],
            'rule'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$', 'qty' => 1]),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p2.id$', 'qty' => 1]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$'], as: 'billingAddress'),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$'], as: 'shippingAddress'),
    ]
    public function testValidateSalesRuleForRestrictedCategories(): void
    {
        $cartId = (int)$this->fixtures->get('cart')->getId();
        $quote = $this->quote->get($cartId);

        $ruleId = $this->fixtures->get('rule')->getId();
        $rule = $this->objectManager->create(Rule::class)->load($ruleId);

        $this->assertFalse($rule->validate($quote->getShippingAddress()));
    }
}
