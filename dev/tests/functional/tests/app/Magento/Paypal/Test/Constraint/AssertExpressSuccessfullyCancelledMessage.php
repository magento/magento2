<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertExpressSuccessfullyCancelledMessage
 * Assert that success message is correct
 */
class AssertExpressSuccessfullyCancelledMessage extends AbstractConstraint
{
    /**
     * Message of successfully cancelled PayPal Express checkout.
     */
    const SUCCESS_MESSAGE = 'Express Checkout has been canceled.';

    /**
     * Assert that success message is correct
     * @param CheckoutCart $checkoutCart
     */
    public function processAssert(CheckoutCart $checkoutCart)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $checkoutCart->getMessagesBlock()->getSuccessMessage(),
            'Wrong success message is displayed.'
        );
    }

    /**
     * Returns string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message on Shopping Cart page is correct.';
    }
}
