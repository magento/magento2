<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert first step on Checkout page is available.
 */
class AssertCustomerIsRedirectedToCheckout extends AbstractConstraint
{
    /**
     * Assert first step on Checkout page is available.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage)
    {
        $checkoutOnepage->open();
        \PHPUnit_Framework_Assert::assertTrue(
            !$checkoutOnepage->getMessagesBlock()->isVisible()
            && $checkoutOnepage->getShippingMethodBlock()->isVisible(),
            'Checkout first step is not available.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Checkout first step is available.';
    }
}
