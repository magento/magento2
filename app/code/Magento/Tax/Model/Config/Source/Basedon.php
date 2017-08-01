<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config\Source;

/**
 * Class \Magento\Tax\Model\Config\Source\Basedon
 *
 * @since 2.0.0
 */
class Basedon implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'shipping', 'label' => __('Shipping Address')],
            ['value' => 'billing', 'label' => __('Billing Address')],
            ['value' => 'origin', 'label' => __("Shipping Origin")]
        ];
    }
}
