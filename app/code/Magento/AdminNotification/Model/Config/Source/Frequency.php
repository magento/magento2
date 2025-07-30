<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdminNotification\Model\Config\Source;

/**
 * AdminNotification update frequency source
 *
 * @codeCoverageIgnore
 * @api
 * @since 100.0.2
 */
class Frequency implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            1 => __('1 Hour'),
            2 => __('2 Hours'),
            6 => __('6 Hours'),
            12 => __('12 Hours'),
            24 => __('24 Hours')
        ];
    }
}
