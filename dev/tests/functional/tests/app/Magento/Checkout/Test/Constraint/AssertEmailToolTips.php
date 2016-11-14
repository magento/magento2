<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that email field tooltips are present.
 */
class AssertEmailToolTips extends AbstractConstraint
{
    /**
     * Email tooltip message.
     *
     * @var string
     */
    private $emailTooltip = 'We\'ll send your order confirmation here.';

    /**
     * Email instructions message.
     *
     * @var string
     */
    private $emailInstructions = 'You can create an account after checkout.';

    /**
     * Assert that email field tooltips are present.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @return void
     */
    public function processAssert(
        CheckoutOnepage $checkoutOnepage
    ) {
        \PHPUnit_Framework_Assert::assertEquals(
            $this->emailTooltip,
            $checkoutOnepage->getShippingBlock()->getEmailTooltip(),
            'Email tooltip is not correct.'
        );

        \PHPUnit_Framework_Assert::assertEquals(
            $this->emailInstructions,
            $checkoutOnepage->getShippingBlock()->getEmailInstructions(),
            'Email instructions are not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Email field tooltips are present.';
    }
}
