<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend tax resource model
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
