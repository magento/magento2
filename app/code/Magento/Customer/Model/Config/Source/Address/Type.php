<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Source model of customer address types
 *
 * @author     Magento Core Team <core@magentocommerce.com>
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
