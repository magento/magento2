<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Constraint;

use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteNew;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\PromoQuoteForm;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section\BlockPromoSalesRuleEditTabCoupons;

/**
 * Assert coupon code generate message.
 */
class AssertCouponCodeSuccessGeneratedMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = '%s coupon(s) have been generated.';

    /**
     * Assert that success message is displayed after generating coupon('s).
     *
     * @param PromoQuoteNew $promoQuoteNew
     * @param array $generateCouponsSettings
     * @return void
     */
    public function processAssert(
        PromoQuoteNew $promoQuoteNew,
        array $generateCouponsSettings
    ) {
        $qty = isset($generateCouponsSettings['qty']) ? $generateCouponsSettings['qty'] : null;

        $expectedMessage = sprintf(self::SUCCESS_MESSAGE, $qty);

        /** @var PromoQuoteForm $salesRuleForm */
        $salesRuleForm = $promoQuoteNew->getSalesRuleForm();

        /** @var BlockPromoSalesRuleEditTabCoupons $manageCouponCodesSection */
        $manageCouponCodesSection = $salesRuleForm->getSection('block_promo_sales_rule_edit_tab_coupons');

        $actualMessage = $manageCouponCodesSection->getSuccessMessage();

        \PHPUnit_Framework_Assert::assertEquals(
            $expectedMessage,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . $expectedMessage
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Coupon generating success message is present.';
    }
}
