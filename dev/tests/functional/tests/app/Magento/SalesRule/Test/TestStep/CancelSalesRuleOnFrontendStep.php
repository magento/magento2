<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Cancel Sales Rule before one page checkout.
 */
class CancelSalesRuleOnFrontendStep implements TestStepInterface
{
    /**
     * Checkout cart page.
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
     * Apply coupon before one page checkout.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->getDiscountCodesBlock()->cancelCouponCode();
        $this->checkoutCart->getTotalsBlock()->waitForUpdatedTotals();
    }
}
