<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend tax resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tax extends \Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend
{
    /**
     * Fetch totals
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    public function fetchTotals(\Magento\Quote\Model\Quote\Address $address)
    {
        $amount = $address->getTaxAmount();
        if ($amount != 0) {
            $address->addTotal(['code' => 'tax', 'title' => __('Tax'), 'value' => $amount]);
        }
        return $this;
    }
}
