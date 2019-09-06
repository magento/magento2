<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Rule\Condition;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\Data\CartInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
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
        /** @var $rule \Magento\SalesRule\Model\Rule */
        $rule = $this->objectManager->get(\Magento\Framework\Registry::class)
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
        /** @var $rule \Magento\SalesRule\Model\Rule */
        $rule = $this->objectManager->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_SalesRule_Sku_Exclude');

        $this->assertEquals(false, $rule->validate($quote));
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
     * Gets quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote($reservedOrderId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();
        return array_pop($items);
    }

    /**
     * Gets rule by name.
     *
     * @param string $name
     * @return \Magento\SalesRule\Model\Rule
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSalesRule(string $name): \Magento\SalesRule\Model\Rule
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('name', $name)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $ruleRepository = $this->objectManager->get(RuleRepositoryInterface::class);
        $items = $ruleRepository->getList($searchCriteria)->getItems();

        $rule = array_pop($items);
        /** @var \Magento\SalesRule\Model\Converter\ToModel $converter */
        $converter = $this->objectManager->get(\Magento\SalesRule\Model\Converter\ToModel::class);

        return $converter->toModel($rule);
    }
}
