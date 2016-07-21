<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Apply Sales Rule on payment information step.
 */
class ApplySalesRuleOnCheckoutStep implements TestStepInterface
{
    /**
     * One Page Checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * SalesRule fixture.
     *
     * @var SalesRule
     */
    protected $salesRule;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param SalesRule $salesRule
     */
    public function __construct(CheckoutOnepage $checkoutOnepage, SalesRule $salesRule = null)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->salesRule = $salesRule;
    }

    /**
     * Apply coupon on payment information step.
     *
     * @return void
     */
    public function run()
    {
        if ($this->salesRule !== null) {
            $this->checkoutOnepage->getDiscountCodesBlock()->applyCouponCode($this->salesRule->getCouponCode());
        }
    }
}
