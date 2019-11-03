<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that countries selected as Top Destinations are at the top in Estimate Shipping and Tax block.
 */
class AssertTopDestinationsInSelect extends AbstractConstraint
{
    /**
     * Assert top destinations in select in Estimate Shipping and Tax block.
     *
     * @param CheckoutCart $checkoutCart
     * @param array $topDestinations
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, array $topDestinations)
    {
        $checkoutCart->open();
        \PHPUnit\Framework\Assert::assertEquals(
            $topDestinations,
            $checkoutCart->getShippingBlock()->getTopCountries(),
            'Top countries are different from the ones selected as Top Destinations.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Countries selected as Top Destinations are at the top in select.';
    }
}
