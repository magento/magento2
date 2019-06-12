<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $checkoutCart->getCartBlock()->waitForLoader();
        if (!$checkoutCart->getCartBlock()->cartIsEmpty()) {
            $checkoutCart->getShippingBlock()->waitForSummaryBlock();
            $checkoutCart->getTotalsBlock()->waitForGrandTotal();
        }
    }
}
