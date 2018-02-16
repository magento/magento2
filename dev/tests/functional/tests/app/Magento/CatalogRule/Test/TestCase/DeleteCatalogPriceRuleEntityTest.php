<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for Delete CatalogPriceRuleEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Catalog Price Rule is created.
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Marketing > Catalog Price Rules
 * 3. Select required catalog price rule from preconditions
 * 4. Click on the "Delete" button
 * 5. Perform all assertions
 *
 * @group Catalog_Price_Rules_(MX)
 * @ZephyrId MAGETWO-25211
 */
class DeleteCatalogPriceRuleEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Page CatalogRuleIndex
     *
     * @var CatalogRuleIndex
     */
    protected $catalogRuleIndex;

    /**
     * Page CatalogRuleNew
     *
     * @var CatalogRuleNew
     */
    protected $catalogRuleNew;

    /**
     * Injection data
     *
     * @param CatalogRuleIndex $catalogRuleIndex
     * @param CatalogRuleNew $catalogRuleNew
     * @return void
     */
    public function __inject(
        CatalogRuleIndex $catalogRuleIndex,
        CatalogRuleNew $catalogRuleNew
    ) {
        $this->catalogRuleIndex = $catalogRuleIndex;
        $this->catalogRuleNew = $catalogRuleNew;
    }

    /**
     * Delete Catalog Price Rule test
     *
     * @param CatalogRule $catalogPriceRule
     * @return void
     */
    public function testDeleteCatalogPriceRule(CatalogRule $catalogPriceRule)
    {
        // Precondition
        $catalogPriceRule->persist();

        $filter = [
            'name' => $catalogPriceRule->getName(),
            'rule_id' => $catalogPriceRule->getId(),
        ];
        // Steps
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $this->catalogRuleNew->getFormPageActions()->delete();
        $this->catalogRuleNew->getModalBlock()->acceptAlert();
    }
}
