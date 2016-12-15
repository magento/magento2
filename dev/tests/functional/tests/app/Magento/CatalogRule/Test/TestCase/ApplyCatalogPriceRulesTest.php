<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Util\Command\Cli\Cron;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleEdit;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Preconditions:
 * 1. Execute before each variation:
 *  - Delete all active catalog price rules
 *  - Create catalog price rule from dataset using Curl
 *
 * Steps:
 * 1. Apply all created rules.
 * 2. Create simple product.
 * 3. Perform all assertions.
 *
 * @group Catalog_Price_Rules
 * @ZephyrId MAGETWO-24780
 */
class ApplyCatalogPriceRulesTest extends AbstractCatalogRuleEntityTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    /* end tags */

    /**
     * Apply catalog price rules.
     *
     * @param array $catalogRules
     * @param CatalogRuleEdit $catalogRuleEdit
     * @param TestStepFactory $stepFactory
     * @param Cron $cron
     * @param bool $isCronEnabled
     * @param Customer $customer
     * @param array $products
     * @return FixtureInterface[]
     */
    public function test(
        array $catalogRules,
        CatalogRuleEdit $catalogRuleEdit,
        TestStepFactory $stepFactory,
        Cron $cron,
        $isCronEnabled = false,
        Customer $customer = null,
        array $products = null
    ) {
        if ($customer !== null) {
            $customer->persist();
        }

        $products = $stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        )->run()['products'];

        foreach ($catalogRules as $catalogRule) {
            foreach ($products as $product) {
                $catalogPriceRule = $this->createCatalogPriceRule($catalogRule, $product, $customer);
                if ($isCronEnabled) {
                    $cron->run();
                    $cron->run();
                } else {
                    $catalogRuleEdit->open(['id' => $catalogPriceRule->getId()]);
                    $this->catalogRuleNew->getFormPageActions()->saveAndApply();
                }
            }
        }
        return ['products' => $products];
    }

    /**
     * Prepare condition for catalog price rule.
     *
     * @param FixtureInterface $product
     * @param array $catalogPriceRule
     * @return array
     */
    private function prepareCondition(FixtureInterface $product, array $catalogPriceRule)
    {
        $result = [];
        $conditionEntity = explode('|', trim($catalogPriceRule['data']['rule'], '[]'))[0];

        switch ($conditionEntity) {
            case 'Category':
                $result['%category_id%'] = $product->getDataFieldConfig('category_ids')['source']->getIds()[0];
                break;
            case 'Attribute':
                /** @var \Magento\Catalog\Test\Fixture\CatalogProductAttribute[] $attrs */
                $attributes = $product->getDataFieldConfig('attribute_set_id')['source']
                    ->getAttributeSet()->getDataFieldConfig('assigned_attributes')['source']->getAttributes();

                $result['%attribute_id%'] = $attributes[0]->getAttributeCode();
                $result['%attribute_value%'] = $attributes[0]->getOptions()[0]['id'];
                break;
        }
        foreach ($result as $key => $value) {
            $catalogPriceRule['data']['rule'] = str_replace($key, $value, $catalogPriceRule['data']['rule']);
        }

        return $catalogPriceRule;
    }

    /**
     * Create customer with customer group and apply customer group to catalog price rule.
     *
     * @param array $catalogPriceRule
     * @param Customer $customer
     * @return array
     */
    private function applyCustomerGroup(array $catalogPriceRule, Customer $customer)
    {
        /** @var \Magento\Customer\Test\Fixture\CustomerGroup $customerGroup */
        $customerGroup = $customer->getDataFieldConfig('group_id')['source']->getCustomerGroup();
        $catalogPriceRule['data']['customer_group_ids']['option_0'] = $customerGroup->getCustomerGroupId();

        return $catalogPriceRule;
    }

    /**
     * Create catalog price rule.
     *
     * @param array $catalogPriceRule
     * @param FixtureInterface $product
     * @param Customer $customer
     * @return CatalogRule
     */
    private function createCatalogPriceRule(
        array $catalogPriceRule,
        FixtureInterface $product,
        Customer $customer = null
    ) {
        if (isset($catalogPriceRule['data']['rule'])) {
            $catalogPriceRule = $this->prepareCondition($product, $catalogPriceRule);
        }
        if ($customer !== null) {
            $catalogPriceRule = $this->applyCustomerGroup($catalogPriceRule, $customer);
        }

        $catalogPriceRule = $this->fixtureFactory->createByCode('catalogRule', $catalogPriceRule);
        $catalogPriceRule->persist();

        return $catalogPriceRule;
    }
}
