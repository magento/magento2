<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class ProceedToCheckoutStep
 * Proceed to checkout
 */
class ProceedToCheckoutStep implements TestStepInterface
{
    /**
     * Checkout cart page
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * @constructor
     * @param CheckoutCart $checkoutCart
     */
    public function __construct(CheckoutCart $checkoutCart)
    {
        $this->checkoutCart = $checkoutCart;
    }

    /**
     * Proceed to checkout
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getProceedToCheckoutBlock()->proceedToCheckout();
    }
}
