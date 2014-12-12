<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\SalesCouponReportView;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCouponReportResult
 * Assert coupon info in report: code, rule name, subtotal, discount on coupons report page
 */
class AssertCouponReportResult extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
        $roleName = $data['coupon_code']->getName();
        $filter = [
            'coupon_code' => $data['coupon_code']->getCouponCode(),
            'rule_name' => $roleName,
            'subtotal' => $currency . number_format($data['price']['subtotal'], 2),
            'discount' => $discount,
        ];
        \PHPUnit_Framework_Assert::assertTrue(
            $salesCouponReportView->getGridBlock()->isRowVisible($filter, false),
            "Coupon '$roleName' is not visible."
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
