<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

/**
 * Class AssertTaxRuleIsNotApplied
 */
class AssertTaxRuleIsNotApplied extends AssertTaxRuleApplying
{
    /**
     * Assert that tax rule is not applied on product in shopping cart.
     *
     * @return void
     */
    protected function assert()
    {
        $errorMessages = [];

        // Preparing data to compare
        $expectedGrandTotal = $this->productSimple->getPrice() + $this->shipping['price'];
        $expectedGrandTotal = number_format($expectedGrandTotal, 2);
        $actualGrandTotal = $this->checkoutCart->getTotalsBlock()->getGrandTotal();

        if ($this->checkoutCart->getTotalsBlock()->isTaxVisible()) {
            $errorMessages[] = 'Tax Rule \'' . $this->taxRuleCode . '\' present in shopping cart.';
        }
        if ($expectedGrandTotal !== $actualGrandTotal) {
            $errorMessages[] = 'Grand Total is not correct.'
                . "\nExpected: " . $expectedGrandTotal
                . "\nActual: " . $actualGrandTotal;
        }

        \PHPUnit\Framework\Assert::assertTrue(empty($errorMessages), implode(";\n", $errorMessages));
    }

    /**
     * Text of Tax Rule is not applied on product in shopping cart.
     *
     * @return string
     */
    public function toString()
    {
        return "Tax rule was not applied on product in shopping cart.";
    }
}
