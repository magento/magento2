<?php

namespace Magento\Checkout\Test\Constraint\Utils;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;

/**
 * Check if cart page is fully loaded.
 */
trait CartPageLoadTrait
{
    /**
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function waitForCartPageLoaded(CheckoutCart $checkoutCart)
    {
        $checkoutCart->getCartBlock()->waitForLoader();
        if (!$checkoutCart->getCartBlock()->cartIsEmpty()) {
            $checkoutCart->getShippingBlock()->waitForSummaryBlock();
            $checkoutCart->getTotalsBlock()->waitForGrandTotal();
        }
    }
}
