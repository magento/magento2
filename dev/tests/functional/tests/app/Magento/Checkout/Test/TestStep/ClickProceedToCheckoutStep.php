<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class ClickProceedToCheckoutStep
 *
 * Proceed to checkout from current page without reloading.
 */
class ClickProceedToCheckoutStep implements TestStepInterface
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
        $this->checkoutCart->getProceedToCheckoutBlock()->proceedToCheckout();
    }
}
