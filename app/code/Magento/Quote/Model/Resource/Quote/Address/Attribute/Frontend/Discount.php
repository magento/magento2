<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Quote\Model\Resource\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend discount resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Discount extends \Magento\Quote\Model\Resource\Quote\Address\Attribute\Frontend
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
            if (strlen($couponCode)) {
                $title .= sprintf(' (%s)', $couponCode);
            }
            $address->addTotal(['code' => 'discount', 'title' => $title, 'value' => -$amount]);
        }
        return $this;
    }
}
