<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Clear shopping cart.
 */
class ClearShoppingCartStep implements TestStepInterface
{
    /**
     * Page of checkout page.
     *
     * @var CheckoutCart
     */
    private $checkoutCart;

    /**
     * @param CheckoutCart $checkoutCart
     */
    public function __construct(CheckoutCart $checkoutCart)
    {
        $this->checkoutCart = $checkoutCart;
    }

    /**
     * Clear shopping cart.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->open()->getCartBlock()->clearShoppingCart();
    }
}
