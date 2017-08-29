<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Proceed to checkout cart page.
 */
class ViewAndEditCartStep implements TestStepInterface
{
    /**
     * Checkout cart page.
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
     * Proceed to checkout cart page.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->open();
    }
}
