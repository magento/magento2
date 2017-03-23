<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

/**
 * Class AssertTaxRuleIsApplied
 */
class AssertTaxRuleIsApplied extends AssertTaxRuleApplying
{
    /**
     * Assert that tax rule is applied on product in shopping cart.
     *
     * @return void
     */
    protected function assert()
    {
        $errorMessages = [];

        // Preparing data to compare
        $taxRate = $this->taxRule->getDataFieldConfig('tax_rate')['source']->getFixture()[0]->getRate();
        $expectedGrandTotal = $this->productSimple->getPrice() + $taxRate + $this->shipping['price'];
        $expectedGrandTotal = number_format($expectedGrandTotal, 2);
        $actualGrandTotal = $this->checkoutCart->getTotalsBlock()->getGrandTotal();

        if ($this->checkoutCart->getTotalsBlock()->isTaxVisible()) {
            $expectedTax = number_format($taxRate, 2);
            $actualTax = $this->checkoutCart->getTotalsBlock()->getTax();
            if ($expectedTax !== $actualTax) {
                $errorMessages[] = 'Tax Rule \'' . $this->taxRuleCode . '\' is applied wrong.'
                    . "\nExpected: " . $expectedTax
                    . "\nActual: " . $actualTax;
            }
        }

        if ($expectedGrandTotal !== $actualGrandTotal) {
            $errorMessages[] = 'Grand Total is not correct.'
                . "\nExpected: " . $expectedGrandTotal
                . "\nActual: " . $actualGrandTotal;
        }

        \PHPUnit_Framework_Assert::assertTrue(empty($errorMessages), implode(";\n", $errorMessages));
    }

    /**
     * Text of Tax Rule is applied on product in shopping cart.
     *
     * @return string
     */
    public function toString()
    {
        return "Tax rule applied on product in shopping cart.";
    }
}
