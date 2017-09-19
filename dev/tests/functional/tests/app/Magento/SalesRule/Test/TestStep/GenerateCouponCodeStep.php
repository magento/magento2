<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteNew;

/**
 * Step for generate coupon codes.
 */
class GenerateCouponCodeStep implements TestStepInterface
{
    /**
     * Page PromoQuoteNew.
     *
     * @var PromoQuoteNew
     */
    private $promoQuoteNew;

    /**
     * Data for coupon generation.
     *
     * @var array
     */
    private $coupon;

    /**
     * GenerateCouponCodeStep constructor.
     *
     * @param PromoQuoteNew $promoQuoteNew
     * @param array $coupon
     */
    public function __construct(PromoQuoteNew $promoQuoteNew, array $coupon)
    {
        $this->promoQuoteNew = $promoQuoteNew;
        $this->coupon = $coupon;
    }

    /**
     * Run generate coupon step flow.
     *
     * @return string
     */
    public function run()
    {
        /** @var Coupons $couponTab */
        $couponTab = $this->promoQuoteNew->getSalesRuleForm()->openTab('coupons')->getTab('coupons');
        $couponTab->fillFormTab($this->coupon);
        $couponTab->clickGenerate();
        /** @var Coupons\Grid $couponsGrid */
        $couponsGrid = $couponTab->getCouponsGrid();

        return $couponsGrid->getFirstCouponCode();
    }
}
