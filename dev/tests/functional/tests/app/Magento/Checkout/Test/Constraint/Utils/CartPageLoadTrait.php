<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint\Utils;

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
