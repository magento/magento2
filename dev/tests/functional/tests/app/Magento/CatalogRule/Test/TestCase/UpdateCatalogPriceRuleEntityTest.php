<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\Product\CategoryIds;
use Magento\CatalogRule\Test\Fixture\CatalogRule;

/**
 * Preconditions:
 * 1. Catalog Price Rule is created.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to MARKETING > Catalog Price Rules.
 * 3. Click Catalog Price Rule from grid.
 * 4. Edit test value(s) according to dataset.
 * 5. Click 'Save'/ 'Apply' button.
 * 6. Create simple product with category.
 * 7. Perform all asserts.
 *
 * @group Catalog_Price_Rules_(MX)
 * @ZephyrId MAGETWO-25187
 */
class UpdateCatalogPriceRuleEntityTest extends AbstractCatalogRuleEntityTest
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Update catalog price rule.
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
            ['dataset' => 'product_with_category']
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

        return ['products' => [$productSimple]];
    }
}
