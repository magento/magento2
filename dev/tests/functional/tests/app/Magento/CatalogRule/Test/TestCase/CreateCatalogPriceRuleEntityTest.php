<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\Fixture\CustomerGroup;

/**
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Marketing > Catalog Price Rules.
 * 3. Press "+" button to start create new catalog price rule.
 * 4. Fill in all data according to data set.
 * 5. Save rule.
 * 6. Perform appropriate assertions.
 *
 * @group Catalog_Price_Rules
 * @ZephyrId MAGETWO-24341
 */
class CreateCatalogPriceRuleEntityTest extends AbstractCatalogRuleEntityTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    /* end tags */

    /**
     * Create Catalog Price Rule.
     *
     * @param CatalogRule $catalogPriceRule
     * @param string $product
     * @param string $conditionEntity
     * @param Customer $customer
     * @return array
     */
    public function test(
        CatalogRule $catalogPriceRule,
        $product = null,
        $conditionEntity = null,
        Customer $customer = null
    ) {
        // Prepare data
        /** @var CatalogProductSimple $productSimple */
        $productSimple = $this->fixtureFactory->createByCode('catalogProductSimple', ['dataset' => $product]);
        $catalogPriceRule = $this->applyCustomerGroup($catalogPriceRule, $customer);
        $replace = $this->prepareCondition($productSimple, $conditionEntity);

        // Steps
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getGridPageActions()->addNew();
        $this->catalogRuleNew->getEditForm()->fill($catalogPriceRule, null, $replace);
        $this->catalogRuleNew->getFormPageActions()->save();
    }

    /**
     * Create customer with customer group and apply customer group to catalog price rule.
     *
     * @param CatalogRule $catalogPriceRule
     * @param Customer|null $customer
     * @return array
     */
    protected function applyCustomerGroup(CatalogRule $catalogPriceRule, Customer $customer = null)
    {
        if ($customer !== null) {
            $customer->persist();
            /** @var \Magento\Customer\Test\Fixture\CustomerGroup $customerGroup */
            $customerGroup = $customer->getDataFieldConfig('group_id')['source']->getCustomerGroup();
            $catalogPriceRule = $this->fixtureFactory->createByCode(
                'catalogRule',
                [
                    'data' => array_merge(
                        $catalogPriceRule->getData(),
                        ['customer_group_ids' => $customerGroup->getCustomerGroupCode()]
                    )
                ]
            );
        }

        return $catalogPriceRule;
    }

    /**
     * Prepare condition for catalog price rule.
     *
     * @param CatalogProductSimple $productSimple
     * @param string $conditionEntity
     * @return array
     */
    private function prepareCondition(CatalogProductSimple $productSimple, $conditionEntity)
    {
        $result = [];

        switch ($conditionEntity) {
            case 'category':
                $result['%category_id%'] = $productSimple->getDataFieldConfig('category_ids')['source']->getIds()[0];
                break;
            case 'attribute':
                /** @var \Magento\Catalog\Test\Fixture\CatalogProductAttribute[] $attrs */
                $attributes = $productSimple->getDataFieldConfig('attribute_set_id')['source']
                    ->getAttributeSet()->getDataFieldConfig('assigned_attributes')['source']->getAttributes();

                $result['%attribute_name%'] = $attributes[0]->getFrontendLabel();
                $result['%attribute_value%'] = $attributes[0]->getOptions()[0]['view'];
                break;
        }

        return [
            'conditions' => [
                'conditions' => $result,
            ],
        ];
    }
}
