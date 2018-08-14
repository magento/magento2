<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertExpressCancelledMessage
 * Assert that success message is correct
 */
class AssertExpressCancelledMessage extends AbstractConstraint
{
    /**
     * Message of cancelled PayPal Express checkout.
     */
    const SUCCESS_MESSAGE = 'Express Checkout has been canceled.';

    /**
     * Assert that success message is correct.
     *
     * @param CheckoutCart $checkoutCart
     */
    public function processAssert(CheckoutCart $checkoutCart)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $checkoutCart->getMessagesBlock()->getSuccessMessage(),
            'Success message about Express Checkout cancellation is not present or wrong.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message about Express Checkout cancellation is present.';
    }
}
