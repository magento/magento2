<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section\RuleInformation;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteIndex;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteNew;

/**
 * Assert that customer group find on cart price rule page.
 */
class AssertCustomerGroupOnCartPriceRuleForm extends AbstractConstraint
{
    /**
     * Assert that customer group find on cart price rule page.
     *
     * @param PromoQuoteIndex $promoQuoteIndex
     * @param PromoQuoteNew $promoQuoteNew
     * @param CustomerGroup $customerGroup
     * @return void
     */
    public function processAssert(
        PromoQuoteIndex $promoQuoteIndex,
        PromoQuoteNew $promoQuoteNew,
        CustomerGroup $customerGroup
    ) {
        $promoQuoteIndex->open();
        $promoQuoteIndex->getGridPageActions()->addNew();
        $promoQuoteNew->getSalesRuleForm()->openSection('rule_information');

        /** @var RuleInformation $ruleInformationTab */
        $ruleInformationTab = $promoQuoteNew->getSalesRuleForm()->getSection('rule_information');
        \PHPUnit_Framework_Assert::assertTrue(
            $ruleInformationTab->isVisibleCustomerGroup($customerGroup),
            "Customer group {$customerGroup->getCustomerGroupCode()} not in cart price rule page."
        );
    }

    /**
     * Success assert of customer group find on cart price rule page.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group find on cart price rule page.';
    }
}
