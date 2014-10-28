<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Reports\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Reports\Test\Page\Adminhtml\SalesCouponReportView;

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
            'discount' => $discount
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
