<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Rule\Condition;

use Magento\Framework\Registry;
use Magento\SalesRule\Model\Rule;

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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Ensure that SalesRules filtering on category validates children products of configurables
     *
     * 1. Load a quote with a configured product and a sales rule set to filter based on category
     * 2. Set product's associated category according to test case
     * 3. Attempt to validate the sales rule against the quote and assert the output is as expected
     *
     * @magentoAppIsolation enabled
     * @param int $categoryId
     * @param bool $expectedResult
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     * @magentoDataFixture Magento/SalesRule/_files/rules_category.php
     * @dataProvider validateProductConditionDataProvider
     * @magentoDbIsolation disabled
     */
    public function testValidateCategorySalesRuleIncludesChildren($categoryId, $expectedResult)
    {
        // Load the quote that contains a child of a configurable product
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class)
            ->load('test_cart_with_configurable', 'reserved_order_id');

        // Load the SalesRule looking for products in a specific category
        /** @var $rule Rule */
        $rule = $this->objectManager->get(Registry::class)
            ->registry('_fixture/Magento_SalesRule_Category');

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
        $validCategoryId = 66;
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
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off.php
     */
    public function testValidateQtySalesRuleWithConfigurable()
    {
        // Load the quote that contains a child of a configurable product with quantity 1.
        $quote = $this->getQuote('test_cart_with_configurable');

        // Load the SalesRule looking for products with quantity 2.
        $rule = $this->getSalesRule('10% Off on orders with two items');

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
                'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
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
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Subselect::class,
                        'attribute' => 'qty',
                        'operator' => '==',
                        'value' => '1',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' =>
                            [
                                [
                                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
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
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Subselect::class,
                        'attribute' => 'qty',
                        'operator' => '==',
                        'value' => '1',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' =>
                            [
                                [
                                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
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
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                        'value' => '1',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' =>
                            [
                                [
                                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
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
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                        'value' => '1',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' =>
                            [
                                [
                                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
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
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                        'value' => '0',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' =>
                            [
                                [
                                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
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
}
