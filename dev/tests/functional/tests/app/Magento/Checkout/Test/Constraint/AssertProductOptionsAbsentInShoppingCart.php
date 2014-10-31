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

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Fixture\Cart;

/**
 * Assert that cart item options for product(s) not display with old options.
 */
class AssertProductOptionsAbsentInShoppingCart extends AssertCartItemsOptions
{
    /**
     * Notice message.
     *
     * @var string
     */
    protected $notice = "\nProduct options from shopping cart are equals to passed from fixture:\n";

    /**
     * Error message for verify options
     *
     * @var string
     */
    protected $errorMessage = '- %s: "%s" equals of "%s"';

    /**
     * Assert that cart item options for product(s) not display with old options.
     *
     * @param CheckoutCart $checkoutCart
     * @param Cart $deletedCart
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Cart $deletedCart)
    {
        parent::processAssert($checkoutCart, $deletedCart);
    }

    /**
     * Check that params are equals.
     *
     * @param mixed $expected
     * @param mixed $actual
     * @return bool
     */
    protected function equals($expected, $actual)
    {
        return (false !== strpos($expected, $actual));
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product with options are absent in shopping cart.';
    }
}
