<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Source model of customer address types
 */
namespace Magento\Customer\Model\Config\Source\Address;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve possible customer address types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            \Magento\Customer\Model\Address\AbstractAddress::TYPE_BILLING => __('Billing Address'),
            \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING => __('Shipping Address')
        ];
    }
}
