<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Proceed to multiple address checkout from cart.
 */
class ProceedToMultipleAddressCheckoutStep implements TestStepInterface
{
    /**
     * Cart index page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * @param CheckoutCart $checkoutCart
     */
    public function __construct(CheckoutCart $checkoutCart)
    {
        $this->checkoutCart = $checkoutCart;
    }

    /**
     * Start checkout with multiple addresses.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getMultipleAddressCheckoutBlock()->multipleAddressesCheckout();
    }
}
