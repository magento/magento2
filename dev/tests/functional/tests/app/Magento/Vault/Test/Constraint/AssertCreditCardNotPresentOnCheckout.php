<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCreditCardNotPresentOnCheckout
 * Assert that success message is correct
 */
class AssertCreditCardNotPresentOnCheckout extends AbstractConstraint
{


    /**
     * Assert that credit card is not present on checkout.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param $deletedCreditCard
     */
    public function processAssert(
        CheckoutOnepage $checkoutOnepage,
        $deletedCreditCard
    ) {
        \PHPUnit_Framework_Assert::assertFalse(
            $checkoutOnepage->getVaultPaymentBlock()->isVaultPaymentPresent($deletedCreditCard),
            'Wrong success message is displayed.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message on My Credit Cards page is correct.';
    }
}
