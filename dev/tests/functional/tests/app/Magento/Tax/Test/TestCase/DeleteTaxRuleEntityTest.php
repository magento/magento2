<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestCase;

use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Tax Rule is created.
 *
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Sales > Tax Rules.
 * 3. Select required tax rule from preconditions.
 * 4. Click on the "Delete Rule" button.
 * 5. Perform all assertions.
 *
 * @group Tax
 * @ZephyrId MAGETWO-20924
 */
class DeleteTaxRuleEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Tax Rule grid page.
     *
     * @var TaxRuleIndex
     */
    protected $taxRuleIndexPage;

    /**
     * Tax Rule new and edit page.
     *
     * @var TaxRuleNew
     */
    protected $taxRuleNewPage;

    /**
     * Create customer.
     *
     * @param Customer $customer
     * @return array
     */
    public function __prepare(Customer $customer)
    {
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Inject pages.
     *
     * @param TaxRuleIndex $taxRuleIndexPage
     * @param TaxRuleNew $taxRuleNewPage
     */
    public function __inject(
        TaxRuleIndex $taxRuleIndexPage,
        TaxRuleNew $taxRuleNewPage
    ) {
        $this->taxRuleIndexPage = $taxRuleIndexPage;
        $this->taxRuleNewPage = $taxRuleNewPage;
    }

    /**
     * Delete Tax Rule Entity test.
     *
     * @param TaxRule $taxRule
     * @return void
     */
    public function testDeleteTaxRule(TaxRule $taxRule)
    {
        // Precondition
        $taxRule->persist();

        // Steps
        $this->taxRuleIndexPage->open();
        $this->taxRuleIndexPage->getTaxRuleGrid()->searchAndOpen(['code' => $taxRule->getCode()]);
        $this->taxRuleNewPage->getFormPageActions()->delete();
        $this->taxRuleNewPage->getModalBlock()->acceptAlert();
    }
}
