<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\SalesRule\Model\Coupon\Quote\UpdateCouponUsages;

/**
 * Increments number of coupon usages before placing order
 */
class CouponUsagesIncrement
{
    /**
     * @var UpdateCouponUsages
     */
    private $updateCouponUsages;

    /**
     * @param UpdateCouponUsages $updateCouponUsages
     */
    public function __construct(UpdateCouponUsages $updateCouponUsages)
    {
        $this->updateCouponUsages = $updateCouponUsages;
    }

    /**
     * Increments number of coupon usages before placing order
     *
     * @param QuoteManagement $subject
     * @param Quote $quote
     * @param array $orderData
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSubmit(QuoteManagement $subject, Quote $quote, $orderData = [])
    {
        $this->updateCouponUsages->execute($quote, true);
    }
}
