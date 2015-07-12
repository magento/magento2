<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;

/**
 * Assert that grand total is equal to expected.
 * Assert that subtotal total in the shopping cart is equals to expected total from data set.
 * Assert that tax amount is equal to expected.
 * Assert that shipping amount is equal to expected.
 */
class AssertEstimateShippingAndTax extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that Order Grand Total is correct on checkout page review block.
     *
     * @var AssertGrandTotalInShoppingCart
     */
    protected $assertGrandTotalInShoppingCart;

    /**
     * Assert that Order Subtotal is correct on checkout page review block.
     *
     * @var AssertSubtotalInShoppingCart
     */
    protected $assertSubtotalInShoppingCart;

    /**
     * Assert that tax amount is correct on checkout page review block.
     *
     * @var AssertTaxInShoppingCart
     */
    protected $assertTaxInShoppingCart;

    /**
     * Assert that shipping amount is correct on checkout page review block.
     *
     * @var AssertShippingInShoppingCart
     */
    protected $assertShippingInShoppingCart;

    /**
     * @constructor
     * @param ObjectManager $objectManager
     * @param AssertSubtotalInShoppingCart $assertSubtotalInShoppingCart
     * @param AssertGrandTotalInShoppingCart $assertGrandTotalInShoppingCart
     * @param AssertTaxInShoppingCart $assertTaxInShoppingCart
     * @param AssertShippingInShoppingCart $assertShippingInShoppingCart
     */
    public function __construct(
        ObjectManager $objectManager,
        AssertSubtotalInShoppingCart $assertSubtotalInShoppingCart,
        AssertGrandTotalInShoppingCart $assertGrandTotalInShoppingCart,
        AssertTaxInShoppingCart $assertTaxInShoppingCart,
        AssertShippingInShoppingCart $assertShippingInShoppingCart
    ) {
        parent::__construct($objectManager);
        $this->assertSubtotalInShoppingCart = $assertSubtotalInShoppingCart;
        $this->assertGrandTotalInShoppingCart = $assertGrandTotalInShoppingCart;
        $this->assertTaxInShoppingCart = $assertTaxInShoppingCart;
        $this->assertShippingInShoppingCart = $assertShippingInShoppingCart;
    }

    /**
     * Assert that grand total is equal to expected.
     * Assert that subtotal total in the shopping cart is equals to expected total from data set.
     * Assert that tax amount is equal to expected.
     * Assert that shipping amount is equal to expected.
     *
     * @param CheckoutCart $checkoutCart
     * @param Cart $cart
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Cart $cart)
    {
        if ($cart->hasData('tax_amount')) {
            $this->assertTaxInShoppingCart->processAssert($checkoutCart, $cart);
        }
        if ($cart->hasData('subtotal')) {
            $this->assertSubtotalInShoppingCart->processAssert($checkoutCart, $cart);
        }
        if ($cart->hasData('grand_total')) {
            $this->assertGrandTotalInShoppingCart->processAssert($checkoutCart, $cart);
        }
        if ($cart->hasData('shipping_amount')) {
            $this->assertShippingInShoppingCart->processAssert($checkoutCart, $cart);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Grand total, subtotal and tax, shipping amounts in the shopping cart equal to expected from data set.';
    }
}
