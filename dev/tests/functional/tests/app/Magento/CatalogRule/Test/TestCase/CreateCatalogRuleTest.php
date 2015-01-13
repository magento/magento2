<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CatalogRule\Test\Fixture\CatalogRule;

/**
 * Test Coverage for Create Catalog Rule
 *
 * Test Flow:
 * 1. Log in as default admin user.
 * 2. Go to Marketing > Catalog Price Rules
 * 3. Press "+" button to start create new catalog price rule
 * 4. Fill in all data according to data set
 * 5. Save rule
 * 6. Apply newly created catalog rule
 * 7. Create simple product
 * 8. Clear cache
 * 9. Perform all assertions
 *
 * @ticketId MAGETWO-23036
 */
class CreateCatalogRuleTest extends AbstractCatalogRuleEntityTest
{
    /**
     * Create Catalog Price Rule
     *
     * @param CatalogRule $catalogPriceRule
     * @return array
     */
    public function testCreate(CatalogRule $catalogPriceRule)
    {
        $productSimple = $this->fixtureFactory->createByCode('catalogProductSimple', ['dataSet' => 'MAGETWO-23036']);
        // Prepare data
        $replace = [
            'conditions' => [
                'conditions' => [
                    '%category_1%' => $productSimple->getDataFieldConfig('category_ids')['source']->getIds()[0],
                ],
            ],
        ];

        // Open Catalog Price Rule page
        $this->catalogRuleIndex->open();

        // Add new Catalog Price Rule
        $this->catalogRuleIndex->getGridPageActions()->addNew();

        // Fill and Save the Form
        $this->catalogRuleNew->getEditForm()->fill($catalogPriceRule, null, $replace);
        $this->catalogRuleNew->getFormPageActions()->save();

        // Apply Catalog Price Rule
        $this->catalogRuleIndex->getGridPageActions()->applyRules();

        // Create simple product
        $productSimple->persist();

        // Flush cache
        $this->adminCache->open();
        $this->adminCache->getActionsBlock()->flushMagentoCache();
        $this->adminCache->getMessagesBlock()->waitSuccessMessage();

        // Prepare data for tear down
        $this->catalogRules[] = $catalogPriceRule;

        return ['product' => $productSimple];
    }
}
