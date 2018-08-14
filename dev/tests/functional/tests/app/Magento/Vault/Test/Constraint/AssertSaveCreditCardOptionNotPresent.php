<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that 'Save for later use' checkbox is not present in credit card form.
 */
class AssertSaveCreditCardOptionNotPresent extends AbstractConstraint
{
    /**
     * Assert that 'Save for later use' checkbox is not present in credit card form.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param string $payment
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $payment)
    {
        \PHPUnit_Framework_Assert::assertFalse(
            $checkoutOnepage->getVaultPaymentBlock()->isVaultVisible($payment),
            'Save for later use checkbox is present.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Save for later use checkbox is not present in credit card form.';
    }
}
