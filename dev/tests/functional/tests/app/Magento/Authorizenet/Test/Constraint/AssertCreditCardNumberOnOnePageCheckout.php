<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Payment\Test\Fixture\CreditCard;

/**
 * Assert credit card fields have set values from fixture.
 */
class AssertCreditCardNumberOnOnePageCheckout extends AbstractConstraint
{
    /**
     * Assert payment form values did persist from fixture after checkout blocks refresh
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param CreditCard $creditCard
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, CreditCard $creditCard)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $creditCard->getCcNumber(),
            $checkoutOnepage->getAuthorizenetBlock()->getCCNumber(),
            'Credit card data did persist with the values from fixture'
        );
    }

    /**
     * Returns string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Credit card data did persist with the values from fixture.';
    }
}
