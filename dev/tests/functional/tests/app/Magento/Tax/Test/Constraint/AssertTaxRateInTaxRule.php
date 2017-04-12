<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;

/**
 * Assert that required tax rate is present in tax rule.
 */
class AssertTaxRateInTaxRule extends AbstractConstraint
{
    /**
     * Assert that required tax rate is present in "Tax Rule Information" on tax rule creation page.
     *
     * @param TaxRuleIndex $taxRuleIndex
     * @param TaxRuleNew $taxRuleNew
     * @param TaxRate $taxRate
     * @return void
     */
    public function processAssert(TaxRuleIndex $taxRuleIndex, TaxRuleNew $taxRuleNew, TaxRate $taxRate)
    {
        $taxRateCode = $taxRate->getCode();
        $taxRuleIndex->open();
        $taxRuleIndex->getGridPageActions()->addNew();

        \PHPUnit_Framework_Assert::assertTrue(
            $taxRuleNew->getTaxRuleForm()->isTaxRateAvailable($taxRateCode),
            "$taxRateCode is not present in Tax Rates multiselect on tax rule creation page."
        );
    }

    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return "Required tax rate is present on Tax Rule page.";
    }
}
