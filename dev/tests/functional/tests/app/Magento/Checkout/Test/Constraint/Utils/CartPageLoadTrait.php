<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

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
    public function waitForCartPageLoaded(CheckoutCart $checkoutCart) : void
    {
        $checkoutCart->getCartBlock()->waitForLoader();
        if (!$checkoutCart->getCartBlock()->cartIsEmpty()) {
            $checkoutCart->getShippingBlock()->waitForSummaryBlock();
            $checkoutCart->getTotalsBlock()->waitForGrandTotal();
        }
    }
}
