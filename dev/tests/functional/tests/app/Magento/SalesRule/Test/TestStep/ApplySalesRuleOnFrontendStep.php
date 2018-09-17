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
     * Sales Rule Discount Code.
     *
     * @var SalesRule
     */
    protected $couponCode;

    /**
     * @constructor
     * @param CheckoutCart $checkoutCart
     * @param SalesRule $salesRule
     * @param string $couponCode
     */
    public function __construct(CheckoutCart $checkoutCart, SalesRule $salesRule = null, $couponCode = null)
    {
        $this->checkoutCart = $checkoutCart;
        $this->salesRule = $salesRule;
        $this->couponCode = $couponCode;
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

        if ($this->couponCode !== null) {
            $this->checkoutCart->getDiscountCodesBlock()->applyCouponCode($this->couponCode);
            $this->checkoutCart->getTotalsBlock()->waitForUpdatedTotals();
        }
    }
}
