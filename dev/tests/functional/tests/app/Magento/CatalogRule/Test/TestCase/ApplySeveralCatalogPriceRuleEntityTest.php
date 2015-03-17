<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Test Creation for Apply several CatalogPriceRuleEntity
 *
 * Test Flow:
 * Preconditions:
 *  1. Execute before each variation:
 *   - Delete all active catalog price rules
 *   - Create catalog price rule from dataSet using Curl
 * Steps:
 *  1. Apply all created rules
 *  2. Create simple product
 *  3. Perform all assertions
 *
 * @group Catalog_Price_Rules_(MX)
 * @ZephyrId MAGETWO-24780
 */
class ApplySeveralCatalogPriceRuleEntityTest extends AbstractCatalogRuleEntityTest
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Apply several catalog price rules
     *
     * @param array $catalogRulesOriginal
     * @return array
     */
    public function testApplySeveralCatalogPriceRules(array $catalogRulesOriginal)
    {
        $this->catalogRuleIndex->open();
        foreach ($catalogRulesOriginal as $key => $catalogPriceRule) {
            if ($catalogPriceRule == '-') {
                continue;
            }
            $this->catalogRules[$key] = $this->fixtureFactory->createByCode(
                'catalogRule',
                ['dataSet' => $catalogPriceRule]
            );
            $this->catalogRules[$key]->persist();

            $filter = [
                'name' => $this->catalogRules[$key]->getName(),
                'rule_id' => $this->catalogRules[$key]->getId(),
            ];
            $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
            $this->catalogRuleNew->getFormPageActions()->saveAndApply();
        }
        // Create product
        $products = $this->objectManager->create(
            '\Magento\Catalog\Test\TestStep\CreateProductsStep',
            ['products' => 'catalogProductSimple::simple_for_salesrule_1']
        )->run();

        return [
            'products' => $products['products'],
            'category' => $products['products'][0]->getDataFieldConfig('category_ids')['source']->getCategories()[0],
        ];
    }
}
