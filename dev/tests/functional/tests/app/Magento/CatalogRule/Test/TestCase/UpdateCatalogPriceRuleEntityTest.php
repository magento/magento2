<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\CatalogProductSimple\CategoryIds;
use Magento\CatalogRule\Test\Fixture\CatalogRule;

/**
 * Test Creation for UpdateCatalogPriceRuleEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Catalog Price Rule is created
 * Steps:
 * 1. Login to backend
 * 2. Navigate to MARKETING > Catalog Price Rules
 * 3. Click Catalog Price Rule from grid
 * 4. Edit test value(s) according to dataSet
 * 5. Click 'Save'/ 'Apply' button
 * 6. Create simple product with category
 * 7. Perform all asserts
 *
 * @group Catalog_Price_Rules_(MX)
 * @ZephyrId MAGETWO-25187
 */
class UpdateCatalogPriceRuleEntityTest extends AbstractCatalogRuleEntityTest
{
    /**
     * Update catalog price rule
     *
     * @param CatalogRule $catalogPriceRule
     * @param CatalogRule $catalogPriceRuleOriginal
     * @param string $saveAction
     * @return array
     */
    public function testUpdateCatalogPriceRule(
        CatalogRule $catalogPriceRule,
        CatalogRule $catalogPriceRuleOriginal,
        $saveAction
    ) {
        // Preconditions
        $catalogPriceRuleOriginal->persist();

        //Prepare data
        $productSimple = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataSet' => 'product_with_category']
        );
        if ($saveAction == 'saveAndApply') {
            /** @var CategoryIds $sourceCategories */
            $sourceCategories = $productSimple->getDataFieldConfig('category_ids')['source'];
            $replace = [
                'conditions' => [
                    'conditions' => [
                        '%category_1%' => $sourceCategories->getIds()[0],
                    ],
                ],
            ];
        } else {
            $replace = [];
        }
        $filter = [
            'name' => $catalogPriceRuleOriginal->getName(),
            'rule_id' => $catalogPriceRuleOriginal->getId(),
        ];

        // Steps
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $this->catalogRuleNew->getEditForm()->fill($catalogPriceRule, null, $replace);
        $this->catalogRuleNew->getFormPageActions()->$saveAction();

        // Create simple product with category
        $productSimple->persist();

        // Prepare data for tear down
        $this->catalogRules[] = $catalogPriceRule;

        return ['product' => $productSimple];
    }
}
