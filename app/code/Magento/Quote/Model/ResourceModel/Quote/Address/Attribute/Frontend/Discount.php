<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend discount resource model
 */
class Discount extends \Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend
{
    /**
     * Fetch discount
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    public function fetchTotals(\Magento\Quote\Model\Quote\Address $address)
    {
        $amount = $address->getDiscountAmount();
        if ($amount != 0) {
            $title = __('Discount');
            $couponCode = $address->getQuote()->getCouponCode();
            if ($couponCode !== null && strlen($couponCode)) {
                $title .= sprintf(' (%s)', $couponCode);
            }
            $address->addTotal(['code' => 'discount', 'title' => $title, 'value' => -$amount]);
        }
        return $this;
    }
}
