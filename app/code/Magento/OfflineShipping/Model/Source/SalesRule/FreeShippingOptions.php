<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\Source\SalesRule;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\OfflineShipping\Model\SalesRule\Rule;

class FreeShippingOptions implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('No')
            ],
            [
                'value' => Rule::FREE_SHIPPING_ITEM,
                'label' => __('For matching items only')
            ],
            [
                'value' => Rule::FREE_SHIPPING_ADDRESS,
                'label' => __('For shipment with matching items')
            ]
        ];
    }
}
