<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Directory\Model\Config\Source;

/**
 * Options provider for weight units list
 *
 * @api
 * @since 100.0.2
 */
class WeightUnit implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'lbs', 'label' => __('lbs')],
            ['value' => 'kgs', 'label' => __('kgs')]
        ];
    }
}
