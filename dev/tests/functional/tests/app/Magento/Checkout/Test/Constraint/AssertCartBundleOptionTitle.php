<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCartBundleOptionTitle
 *
 * Assert cart bundle option title is shown properly after change
 */
class AssertCartBundleOptionTitle extends AbstractConstraint
{
    /**
     * Assert cart bundle option title
     *
     * @param CheckoutCart $checkoutCart
     * @param string $optionTitle
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, $optionTitle)
    {
        $checkoutCart->open();
        \PHPUnit_Framework_Assert::assertEquals(
            $checkoutCart->getCartItemBlock()->getOptionsName(),
            $optionTitle
        );
    }

    /**
     * Returns string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert cart bundle option title is shown properly after change.';
    }
}
