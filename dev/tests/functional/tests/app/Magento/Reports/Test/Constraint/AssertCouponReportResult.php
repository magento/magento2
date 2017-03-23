<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\SalesCouponReportView;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCouponReportResult
 * Assert coupon info in report: code, rule name, subtotal, discount on coupons report page
 */
class AssertCouponReportResult extends AbstractConstraint
{
    /**
     * Assert coupon info in report: code, rule name, subtotal, discount on coupons report page
     *
     * @param SalesCouponReportView $salesCouponReportView
     * @param OrderInjectable $order
     * @param string $currency
     * @return void
     */
    public function processAssert(SalesCouponReportView $salesCouponReportView, OrderInjectable $order, $currency = '$')
    {
        $data = $order->getData();
        $discount = $data['price']['discount'] != 0
            ? '-' . $currency . number_format($data['price']['discount'], 2)
            : $currency . '0.00';
        $couponCode = $data['coupon_code']->getCouponCode();
        $filter = [
            'coupon_code' => $couponCode,
            'rule_name' => $data['coupon_code']->getName(),
            'subtotal' => $currency . number_format($data['price']['subtotal'], 2),
            'discount' => $discount,
        ];
        \PHPUnit_Framework_Assert::assertTrue(
            $salesCouponReportView->getGridBlock()->isRowVisible($filter, false),
            "Coupon with code - '$couponCode' is not visible."
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Coupon info is correct on coupons report page.";
    }
}
