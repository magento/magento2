<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend shipping resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shipping extends \Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend
{
    /**
     * Fetch totals
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    public function fetchTotals(\Magento\Quote\Model\Quote\Address $address)
    {
        $amount = $address->getShippingAmount();
        if ($amount != 0) {
            $title = __('Shipping & Handling');
            if ($address->getShippingDescription()) {
                $title .= sprintf(' (%s)', $address->getShippingDescription());
            }
            $address->addTotal(
                ['code' => 'shipping', 'title' => $title, 'value' => $address->getShippingAmount()]
            );
        }
        return $this;
    }
}
