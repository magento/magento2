<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section\BlockPromoSalesRuleEditTabCoupons;

/**
 * Grid class for coupon codes.
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Return generated coupon codes as array of codes.
     *
     * @return array
     */
    public function getCouponCodes()
    {
        /** @var array $generatedCouponCodes */
        $generatedCouponCodes = $this->getRowsData(['code']);
        $generatedCouponCodes = array_map(
            function ($element) {
                return $element['code'];
            },
            $generatedCouponCodes
        );

        return $generatedCouponCodes;
    }
}
