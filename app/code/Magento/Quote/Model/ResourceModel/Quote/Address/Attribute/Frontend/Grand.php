<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend grand resource model
 */
class Grand extends \Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend
{
    /**
     * Fetch grand total
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    public function fetchTotals(\Magento\Quote\Model\Quote\Address $address)
    {
        $address->addTotal(
            [
                'code' => 'grand_total',
                'title' => __('Grand Total'),
                'value' => $address->getGrandTotal(),
                'area' => 'footer',
            ]
        );
        return $this;
    }
}
