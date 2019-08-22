<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCartIsEmpty
 * Check that Shopping Cart is empty
 */
class AssertCartIsEmpty extends AbstractConstraint
{
    /**
     * Text of empty cart.
     */
    const TEXT_EMPTY_CART = 'You have no items in your shopping cart. Click here to continue shopping.';

    /**
     * Check that Shopping Cart is empty, opened page contains text "You have no items in your shopping cart.
     * Click here to continue shopping." where 'here' is link that leads to index page
     *
     * @param CheckoutCart $checkoutCart
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CheckoutCart $checkoutCart,
        BrowserInterface $browser
    ): void {
        $checkoutCart->open();
        $cartEmptyBlock = $checkoutCart->getCartEmptyBlock();

        \PHPUnit\Framework\Assert::assertEquals(
            self::TEXT_EMPTY_CART,
            $cartEmptyBlock->getText(),
            'Wrong text on empty cart page.'
        );

        $cartEmptyBlock->clickLinkToMainPage();
        $this->assertUrlEqual(
            $_ENV['app_frontend_url'],
            $browser->getUrl(),
            true,
            'Wrong link to main page on empty cart page: expected - ' . $_ENV['app_frontend_url']
            . ', actual - ' . $browser->getUrl()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Shopping Cart is empty.';
    }

    /**
     * Asserts that two urls are equal
     *
     * @param string $expectedUrl
     * @param string $actualUrl
     * @param bool $ignoreScheme
     * @param string $message
     * @return void
     */
    private function assertUrlEqual(
        string $expectedUrl,
        string $actualUrl,
        bool $ignoreScheme = false,
        string $message = ''
    ): void {
        $urlArray1 = parse_url($expectedUrl);
        $urlArray2 = parse_url($actualUrl);
        if ($ignoreScheme) {
            unset($urlArray1['scheme']);
            unset($urlArray2['scheme']);
        }
        \PHPUnit\Framework\Assert::assertTrue(
            $urlArray1 === $urlArray2,
            $message
        );
    }
}
