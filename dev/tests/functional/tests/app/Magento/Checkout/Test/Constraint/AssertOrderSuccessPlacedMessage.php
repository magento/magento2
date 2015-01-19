<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderSuccessPlacedMessage
 * Assert that success message is correct
 */
class AssertOrderSuccessPlacedMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Message of success checkout
     */
    const SUCCESS_MESSAGE = 'Thank you for your purchase!';

    /**
     * Assert that success message is correct
     *
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @return void
     */
    public function processAssert(CheckoutOnepageSuccess $checkoutOnepageSuccess)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $checkoutOnepageSuccess->getTitleBlock()->getTitle(),
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
        return 'Success message on Checkout onepage success page is correct.';
    }
}
