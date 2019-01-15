<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

/**
 * Class AssertTaxWithCrossBorderApplied
 * Checks that prices on category, product and cart pages are equal for both customers
 */
class AssertTaxWithCrossBorderApplied extends AbstractAssertTaxWithCrossBorderApplying
{
    /**
     * Assert prices on category, product and cart pages are equal for both customers
     *
     * @param array $actualPrices
     * @return void
     */
    public function assert($actualPrices)
    {
        //Prices verification
        \PHPUnit\Framework\Assert::assertEmpty(
            array_diff($actualPrices[0], $actualPrices[1]),
            'Prices for customers should be equal. Cross border is not applied.'
        );
    }

    /**
     * Text of Cross Border is applied
     *
     * @return string
     */
    public function toString()
    {
        return 'Cross border trading is applied on front.';
    }
}
