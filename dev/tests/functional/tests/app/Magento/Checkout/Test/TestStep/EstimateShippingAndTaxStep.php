<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Address;
use Magento\Checkout\Test\Constraint\AssertEstimateShippingAndTax;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Estimate Shipping and Tax
 */
class EstimateShippingAndTaxStep implements TestStepInterface
{
    /**
     * Page of checkout page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Customer Address.
     *
     * @var Address
     */
    protected $address;

    /**
     * Assert that grand total is equal to expected.
     * Assert that subtotal total in the shopping cart is equals to expected total from data set.
     * Assert that tax amount is equal to expected.
     * Assert that shipping amount is equal to expected.
     *
     * @var AssertEstimateShippingAndTax
     */
    protected $assertEstimateShippingAndTax;

    /**
     * Cart data.
     *
     * @var Cart
     */
    protected $cart;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Shipping method title and shipping service name.
     *
     * @var array
     */
    protected $shipping;

    /**
     * Products.
     *
     * @var array
     */
    protected $products;

    /**
     * @constructor
     * @param CheckoutCart $checkoutCart
     * @param Address $address
     * @param AssertEstimateShippingAndTax $assertEstimateShippingAndTax
     * @param FixtureFactory $fixtureFactory
     * @param array $products
     * @param array $shipping
     * @param Cart|null $cart
     */
    public function __construct(
        CheckoutCart $checkoutCart,
        Address $address,
        AssertEstimateShippingAndTax $assertEstimateShippingAndTax,
        FixtureFactory $fixtureFactory,
        array $products,
        array $shipping = [],
        Cart $cart = null
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->address = $address;
        $this->assertEstimateShippingAndTax = $assertEstimateShippingAndTax;
        $this->fixtureFactory = $fixtureFactory;
        $this->products = $products;
        $this->shipping = $shipping;
        $this->cart = $cart;
    }

    /**
     * Estimate shipping and tax and process assertions for totals.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->waitCartContainerLoading();
        /** @var \Magento\Checkout\Test\Fixture\Cart $cart */
        if ($this->cart !== null) {
            $cart = $this->fixtureFactory->createByCode(
                'cart',
                ['data' => array_merge($this->cart->getData(), ['items' => ['products' => $this->products]])]
            );
            $this->checkoutCart->getShippingBlock()->fillEstimateShippingAndTax($this->address);
            if (!empty($this->shipping)) {
                $this->checkoutCart->getShippingBlock()->selectShippingMethod($this->shipping);
            }
            $this->checkoutCart->getTotalsBlock()->waitForUpdatedTotals();
            $this->assertEstimateShippingAndTax->processAssert($this->checkoutCart, $cart, false);
        }
    }
}
