<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Apply Sales Rule before one page checkout.
 */
class ApplySalesRuleOnFrontendStep implements TestStepInterface
{
    /**
     * Checkout cart page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * SalesRule fixture.
     *
     * @var SalesRule
     */
    protected $salesRule;

    /**
     * @constructor
     * @param CheckoutCart $checkoutCart
     * @param SalesRule $salesRule
     */
    public function __construct(CheckoutCart $checkoutCart, SalesRule $salesRule = null)
    {
        $this->checkoutCart = $checkoutCart;
        $this->salesRule = $salesRule;
    }

    /**
     * Apply coupon before one page checkout.
     *
     * @return void
     */
    public function run()
    {
        if ($this->salesRule !== null) {
            $this->checkoutCart->getDiscountCodesBlock()->applyCouponCode($this->salesRule->getCouponCode());
            $this->checkoutCart->getTotalsBlock()->waitForUpdatedTotals();
        }
    }
}
