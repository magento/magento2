<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Fixture\Cart;
use Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Checkout\Test\Constraint\AssertGrandTotalInShoppingCart;
use Magento\Checkout\Test\Constraint\AssertSubtotalInShoppingCart;
use Magento\Checkout\Test\Constraint\AssertTaxInShoppingCart;
use Magento\Checkout\Test\Constraint\AssertShippingInShoppingCart;
use Mtf\TestStep\TestStepInterface;

/**
 * Class EstimateShippingAndTaxStep
 * Estimate Shipping and Tax
 */
class EstimateShippingAndTaxStep implements TestStepInterface
{
    /**
     * Page of checkout page
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Customer Address
     *
     * @var AddressInjectable
     */
    protected $addressInjectable;

    /**
     * Assert that Order Grand Total is correct on checkout page review block
     *
     * @var AssertGrandTotalInShoppingCart
     */
    protected $assertGrandTotalInShoppingCart;

    /**
     * Assert that Order Subtotal is correct on checkout page review block
     *
     * @var AssertSubtotalInShoppingCart
     */
    protected $assertSubtotalInShoppingCart;

    /**
     * Assert that tax amount is correct on checkout page review block
     *
     * @var AssertTaxInShoppingCart
     */
    protected $assertTaxInShoppingCart;

    /**
     * Assert that shipping amount is correct on checkout page review block
     *
     * @var AssertShippingInShoppingCart
     */
    protected $assertShippingInShoppingCart;

    /**
     * Grand total price
     *
     * @var Cart
     */
    protected $cart;

    /**
     * fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Shipping
     *
     * @var array
     */
    protected $shipping;

    /**
     * Products
     *
     * @var array
     */
    protected $products;

    /**
     * @constructor
     * @param CheckoutCart $checkoutCart
     * @param AddressInjectable $addressInjectable
     * @param AssertSubtotalInShoppingCart $assertSubtotalInShoppingCart
     * @param AssertGrandTotalInShoppingCart $assertGrandTotalInShoppingCart
     * @param AssertTaxInShoppingCart $assertTaxInShoppingCart
     * @param AssertShippingInShoppingCart $assertShippingInShoppingCart
     * @param Cart $cart
     * @param FixtureFactory $fixtureFactory
     * @param array $shipping
     * @param array $products
     */
    public function __construct(
        CheckoutCart $checkoutCart,
        AddressInjectable $addressInjectable,
        AssertSubtotalInShoppingCart $assertSubtotalInShoppingCart,
        AssertGrandTotalInShoppingCart $assertGrandTotalInShoppingCart,
        AssertTaxInShoppingCart $assertTaxInShoppingCart,
        AssertShippingInShoppingCart $assertShippingInShoppingCart,
        Cart $cart,
        FixtureFactory $fixtureFactory,
        array $shipping,
        array $products
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->addressInjectable = $addressInjectable;
        $this->assertSubtotalInShoppingCart = $assertSubtotalInShoppingCart;
        $this->assertGrandTotalInShoppingCart = $assertGrandTotalInShoppingCart;
        $this->assertTaxInShoppingCart = $assertTaxInShoppingCart;
        $this->assertShippingInShoppingCart = $assertShippingInShoppingCart;
        $this->cart = $cart;
        $this->fixtureFactory = $fixtureFactory;
        $this->shipping = $shipping;
        $this->products = $products;
    }

    /**
     * Add products to the cart
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->getShippingBlock()->fillEstimateShippingAndTax($this->addressInjectable);
        if ($this->shipping['shipping_service'] !== '-') {
            $this->checkoutCart->getShippingBlock()->selectShippingMethod($this->shipping);
        }
        $this->fixtureFactory->createByCode('cart', ['items' => ['products' => $this->products]]);
//            $this->assertSubtotalInShoppingCart->processAssert($this->checkoutCart, $this->cart);
        $this->assertGrandTotalInShoppingCart->processAssert($this->checkoutCart, $this->cart);
        $this->assertTaxInShoppingCart->processAssert($this->checkoutCart, $this->cart);
        $this->assertShippingInShoppingCart->processAssert($this->checkoutCart, $this->cart);
    }
}
