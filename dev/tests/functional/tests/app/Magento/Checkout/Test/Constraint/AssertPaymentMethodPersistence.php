<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert payment method is absent on Checkout Payment Page.
 */
class AssertPaymentMethodPersistence extends AbstractConstraint
{
    /**
     * Assert payment method is absent on Checkout Payment Page.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $payment
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, array $payment)
    {
        \PHPUnit\Framework\Assert::assertFalse(
            $checkoutOnepage->getPaymentBlock()->isVisiblePaymentMethod($payment),
            'Payment method' . $payment['method']. ' is present on Checkout Payment Page.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Payment method is absent on Checkout Payment Page';
    }
}
