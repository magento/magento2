<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * 6. Apply newly created catalog rule.
 * 7. Create simple product.
 * 8. Clear cache.
 * 9. Perform all assertions.
 *
 * @ZephyrId MAGETWO-23036
 */
class CreateCatalogRuleTest extends AbstractCatalogRuleEntityTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Create Catalog Price Rule.
     *
     * @param CatalogRule $catalogPriceRule
     * @param string $product
     * @param string $conditionEntity
     * @param bool $isWithApply
     * @param Customer $customer
     * @return array
     */
    public function testCreate(
        CatalogRule $catalogPriceRule,
        $product,
        $conditionEntity,
        $isWithApply = true,
        Customer $customer = null
    ) {
        /** @var CatalogProductSimple $productSimple */
        $productSimple = $this->fixtureFactory->createByCode('catalogProductSimple', ['dataset' => $product]);
        // Prepare data
        $catalogPriceRule = $this->applyCustomerGroup($catalogPriceRule, $customer);
        $replace = $this->prepareCondition($productSimple, $conditionEntity);

        // Open Catalog Price Rule page
        $this->catalogRuleIndex->open();

        // Add new Catalog Price Rule
        $this->catalogRuleIndex->getGridPageActions()->addNew();

        // Fill and Save the Form
        $this->catalogRuleNew->getEditForm()->fill($catalogPriceRule, null, $replace);
        $this->catalogRuleNew->getFormPageActions()->save();

        if ($isWithApply) {
            // Apply Catalog Price Rule
            $this->catalogRuleIndex->getGridPageActions()->applyRules();
        }

        // Create simple product
        $productSimple->persist();

        // Flush cache
        $this->adminCache->open();
        $this->adminCache->getActionsBlock()->flushMagentoCache();
        $this->adminCache->getMessagesBlock()->waitSuccessMessage();

        return [
            'products' => [$productSimple],
            'category' => $productSimple->getDataFieldConfig('category_ids')['source']->getCategories()[0],
        ];
    }

    /**
     * Create customer with customer group and apply customer group to catalog price rule.
     *
     * @param CatalogRule $catalogPriceRule
     * @param Customer|null $customer
     * @return CustomerGroup
     */
    public function applyCustomerGroup(CatalogRule $catalogPriceRule, Customer $customer = null)
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
    protected function prepareCondition(CatalogProductSimple $productSimple, $conditionEntity)
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
