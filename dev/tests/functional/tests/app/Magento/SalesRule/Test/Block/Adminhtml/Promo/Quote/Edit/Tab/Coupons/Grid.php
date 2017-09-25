<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons;

/**
 * Generated coupon codes grid,
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Get first generated coupon code.
     *
     * @return string
     */
    public function getFirstCouponCode()
    {
        $couponsCodes = $this->getRowsData(['code']);
        return array_shift($couponsCodes)['code'];
    }
}
