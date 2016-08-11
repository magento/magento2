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
     * @param CatalogProductSimple $product
     * @param CatalogRuleEdit $catalogRuleEdit
     * @param Cron $cron
     * @param bool $isCronEnabled
     * @param Customer $customer
     * @return array
     */
    public function test(
        array $catalogRules,
        CatalogProductSimple $product,
        CatalogRuleEdit $catalogRuleEdit,
        Cron $cron,
        $isCronEnabled = false,
        Customer $customer = null
    ) {
        $product->persist();

        foreach ($catalogRules as $catalogPriceRule) {
            $catalogPriceRule = $this->createCatalogPriceRule($catalogPriceRule, $product, $customer);

            if ($isCronEnabled) {
                $cron->run();
                $cron->run();
            } else {
                $catalogRuleEdit->open(['id' => $catalogPriceRule->getId()]);
                $this->catalogRuleNew->getFormPageActions()->saveAndApply();
            }
        }

        return ['products' => [$product]];
    }

    /**
     * Prepare condition for catalog price rule.
     *
     * @param CatalogProductSimple $productSimple
     * @param array $catalogPriceRule
     * @return array
     */
    private function prepareCondition(CatalogProductSimple $productSimple, array $catalogPriceRule)
    {
        $result = [];
        $conditionEntity = explode('|', trim($catalogPriceRule['data']['rule'], '[]'))[0];

        switch ($conditionEntity) {
            case 'Category':
                $result['%category_id%'] = $productSimple->getDataFieldConfig('category_ids')['source']->getIds()[0];
                break;
            case 'Attribute':
                /** @var \Magento\Catalog\Test\Fixture\CatalogProductAttribute[] $attrs */
                $attributes = $productSimple->getDataFieldConfig('attribute_set_id')['source']
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
        $customer->persist();
        /** @var \Magento\Customer\Test\Fixture\CustomerGroup $customerGroup */
        $customerGroup = $customer->getDataFieldConfig('group_id')['source']->getCustomerGroup();
        $catalogPriceRule['data']['customer_group_ids']['option_0'] = $customerGroup->getCustomerGroupId();

        return $catalogPriceRule;
    }

    /**
     * Create catalog price rule.
     *
     * @param CatalogProductSimple $product
     * @param array $catalogPriceRule
     * @param Customer $customer
     * @return CatalogRule
     */
    private function createCatalogPriceRule(
        array $catalogPriceRule,
        CatalogProductSimple $product,
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
