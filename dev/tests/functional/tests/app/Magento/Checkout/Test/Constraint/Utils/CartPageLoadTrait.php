<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
    public function waitForCartPageLoaded(CheckoutCart $checkoutCart)
=======
    public function waitForCartPageLoaded(CheckoutCart $checkoutCart) : void
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $checkoutCart->getCartBlock()->waitForLoader();
        if (!$checkoutCart->getCartBlock()->cartIsEmpty()) {
            $checkoutCart->getShippingBlock()->waitForSummaryBlock();
            $checkoutCart->getTotalsBlock()->waitForGrandTotal();
        }
    }
}
