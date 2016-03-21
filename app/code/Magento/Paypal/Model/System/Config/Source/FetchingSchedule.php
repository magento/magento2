<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Source model for available settlement report fetching intervals
 */
class FetchingSchedule implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            1 => __("Daily"),
            3 => __("Every 3 days"),
            7 => __("Every 7 days"),
            10 => __("Every 10 days"),
            14 => __("Every 14 days"),
            30 => __("Every 30 days"),
            40 => __("Every 40 days")
        ];
    }
}
