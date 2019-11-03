<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert that success message about canceled order is present and correct.
 */
class AssertCancelSuccessMessageInShoppingCart extends AbstractConstraint
{
    /**
     * Cancel success message text.
     */
    const SUCCESS_MESSAGE = 'Your purchase process has been cancelled.';

    /**
     * Assert that success message about canceled order is present and correct.
     *
     * @param CheckoutCart $checkoutCart
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, BrowserInterface $browser)
    {
        $path = $checkoutCart::MCA;
        $browser->waitUntil(
            function () use ($browser, $path) {
                return $_ENV['app_frontend_url'] . $path . '/' === $browser->getUrl() . 'index/' ? true : null;
            }
        );

        $actualMessage = $checkoutCart->getMessagesBlock()->getSuccessMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Success message is not present or has wrong text.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cancel success message is present or has a correct text.';
    }
}
