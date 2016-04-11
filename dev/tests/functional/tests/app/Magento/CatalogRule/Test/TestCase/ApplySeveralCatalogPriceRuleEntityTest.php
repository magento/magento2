<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;

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
     * Apply several catalog price rules.
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
            $catalogRules[$key] = $this->fixtureFactory->createByCode(
                'catalogRule',
                ['dataset' => $catalogPriceRule]
            );
            $catalogRules[$key]->persist();

            $filter = [
                'name' => $catalogRules[$key]->getName(),
                'rule_id' => $catalogRules[$key]->getId(),
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
