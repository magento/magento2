<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

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
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Check that Shopping Cart is empty, opened page contains text "You have no items in your shopping cart.
     * Click here to continue shopping." where 'here' is link that leads to index page
     *
     * @param CheckoutCart $checkoutCart
     * @param Browser $browser
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Browser $browser)
    {
        $checkoutCart->open();
        $cartEmptyBlock = $checkoutCart->getCartEmptyBlock();

        \PHPUnit_Framework_Assert::assertEquals(
            self::TEXT_EMPTY_CART,
            $cartEmptyBlock->getText(),
            'Wrong text on empty cart page.'
        );

        $cartEmptyBlock->clickLinkToMainPage();
        \PHPUnit_Framework_Assert::assertEquals(
            $_ENV['app_frontend_url'],
            $browser->getUrl(),
            'Wrong link to main page on empty cart page.'
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
}
