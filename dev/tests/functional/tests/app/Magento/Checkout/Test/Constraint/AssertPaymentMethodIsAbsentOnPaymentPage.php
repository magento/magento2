<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert payment method is not available on OnePage Checkout.
 */
class AssertPaymentMethodIsAbsentOnPaymentPage extends AbstractConstraint
{
    /**
     * Assert payment method is not available on OnePage Checkout.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $payment
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $payment)
    {
        \PHPUnit_Framework_Assert::assertFalse(
            $checkoutOnepage->getPaymentBlock()->isVisiblePaymentMethod($payment),
            'Payment method' . $payment['method']. ' is present on payment method block.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Payment method is not available on OnePage Checkout';
    }
}
