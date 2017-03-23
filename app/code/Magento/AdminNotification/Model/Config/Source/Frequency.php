<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\Config\Source;

/**
 * AdminNotification update frequency source
 *
 * @codeCoverageIgnore
 */
class Frequency implements \Magento\Framework\Option\ArrayInterface
{
    /**
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
