<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

/**
 * Class AssertTaxWithCrossBorderNotApplied
 * Checks that prices on category, product and cart pages are different for each customer
 */
class AssertTaxWithCrossBorderNotApplied extends AbstractAssertTaxWithCrossBorderApplying
{
    /**
     * Assert prices on category, product and cart pages are different for each customer
     *
     * @param array $actualPrices
     * @return void
     */
    public function assert($actualPrices)
    {
        //Prices verification
        \PHPUnit_Framework_Assert::assertNotEmpty(
            array_diff($actualPrices[0], $actualPrices[1]),
            'Prices for customers should be different.'
        );
    }

    /**
     * Text of Cross Border is applied
     *
     * @return string
     */
    public function toString()
    {
        return 'Cross border trading is not applied on front.';
    }
}
