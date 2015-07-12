<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\CatalogRule\Test\Fixture\CatalogRule;

/**
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Marketing > Catalog Price Rules.
 * 3. Press "+" button to start create new catalog price rule.
 * 4. Fill in all data according to data set.
 * 5. Save rule.
 * 6. Perform appropriate assertions.
 *
 * @group Catalog_Price_Rules_(MX)
 * @ZephyrId MAGETWO-24341
 */
class CreateCatalogPriceRuleEntityTest extends AbstractCatalogRuleEntityTest
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Create Catalog Price Rule
     *
     * @param CatalogRule $catalogPriceRule
     * @return void
     */
    public function testCreateCatalogPriceRule(CatalogRule $catalogPriceRule)
    {
        // Steps
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getGridPageActions()->addNew();
        $this->catalogRuleNew->getEditForm()->fill($catalogPriceRule);
        $this->catalogRuleNew->getFormPageActions()->save();
    }
}
