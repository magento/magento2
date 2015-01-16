<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestCase;

use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Mtf\ObjectManager;
use Mtf\TestCase\Injectable;

/**
 * Test Flow:
 *
 * 1. Log in as default admin user.
 * 2. Go to Stores > Tax Rules.
 * 3. Click 'Add New Tax Rule' button.
 * 4. Fill in data according to dataSet
 * 5. Save Tax Rule.
 * 6. Perform all assertions.
 *
 * @group Tax_(CS)
 * @ZephyrId MAGETWO-20913
 */
class CreateTaxRuleEntityTest extends Injectable
{
    /**
     * Tax rule index page.
     *
     * @var TaxRuleIndex
     */
    protected $taxRuleIndexPage;

    /**
     * Tax rule form page.
     *
     * @var TaxRuleNew
     */
    protected $taxRuleNewPage;

    /**
     * Injection data.
     *
     * @param TaxRuleIndex $taxRuleIndexPage
     * @param TaxRuleNew $taxRuleNewPage
     * @return void
     */
    public function __inject(
        TaxRuleIndex $taxRuleIndexPage,
        TaxRuleNew $taxRuleNewPage
    ) {
        $this->taxRuleIndexPage = $taxRuleIndexPage;
        $this->taxRuleNewPage = $taxRuleNewPage;
    }

    /**
     * Test create tax rule.
     *
     * @param TaxRule $taxRule
     * @return void
     */
    public function testCreateTaxRule(TaxRule $taxRule)
    {
        // Steps
        $this->taxRuleIndexPage->open();
        $this->taxRuleIndexPage->getGridPageActions()->addNew();
        $this->taxRuleNewPage->getTaxRuleForm()->fill($taxRule);
        $this->taxRuleNewPage->getFormPageActions()->save();
    }

    /**
     * Delete all tax rules.
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        ObjectManager::getInstance()->create('Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep', [])->run();
    }
}
