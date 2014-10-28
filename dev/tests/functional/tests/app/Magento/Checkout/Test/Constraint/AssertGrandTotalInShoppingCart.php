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
use Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Class AssertGrandTotalInShoppingCart
 * Assert that grand total is equal to expected
 */
class AssertGrandTotalInShoppingCart extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that grand total is equal to expected
     *
     * @param CheckoutCart $checkoutCart
     * @param Cart $cart
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Cart $cart)
    {
        $checkoutCart->open();

        $fixtureGrandTotal = number_format($cart->getGrandTotal(), 2);
        $pageGrandTotal = $checkoutCart->getTotalsBlock()->getGrandTotal();
        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureGrandTotal,
            $pageGrandTotal,
            'Grand total price in the shopping cart not equals to grand total price from fixture.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Grand total price in the shopping cart equals to expected grand total price from data set.';
    }
}
