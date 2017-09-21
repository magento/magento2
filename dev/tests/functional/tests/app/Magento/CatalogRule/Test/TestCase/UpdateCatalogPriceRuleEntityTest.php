<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\Product\CategoryIds;
use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Util\Command\Cli\Cron;

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
 * @group Catalog_Price_Rules
 * @ZephyrId MAGETWO-25187
 */
class UpdateCatalogPriceRuleEntityTest extends AbstractCatalogRuleEntityTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Update catalog price rule.
     *
     * @param CatalogRule $catalogPriceRule
     * @param CatalogRule $catalogPriceRuleOriginal
     * @param Customer $customer
     * @param Cron $cron
     * @param string $saveAction
     * @param bool $isCronEnabled
     * @return array
     */
    public function test(
        CatalogRule $catalogPriceRule,
        CatalogRule $catalogPriceRuleOriginal,
        Cron $cron,
        $saveAction,
        Customer $customer = null,
        $isCronEnabled = false
    ) {
        // Preconditions
        $catalogPriceRuleOriginal->persist();

        if ($customer !== null) {
            $customer->persist();
        }

        if ($isCronEnabled) {
            $cron->run();
            $cron->run();
        }

        // Prepare data
        $productSimple = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataset' => 'product_with_category']
        );

        /** @var CategoryIds $sourceCategories */
        $sourceCategories = $productSimple->getDataFieldConfig('category_ids')['source'];
        $replace = [
            'conditions' => [
                'conditions' => [
                    '%category_1%' => $sourceCategories->getIds()[0],
                ],
            ],
        ];
        $filter = [
            'name' => $catalogPriceRuleOriginal->getName(),
            'rule_id' => $catalogPriceRuleOriginal->getId(),
        ];

        // Steps
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $this->catalogRuleNew->getEditForm()->fill($catalogPriceRule, null, $replace);
        $this->catalogRuleNew->getFormPageActions()->$saveAction();

        if ($isCronEnabled) {
            $cron->run();
            $cron->run();
        }

        // Create simple product with category
        $productSimple->persist();

        return ['products' => [$productSimple]];
    }
}
