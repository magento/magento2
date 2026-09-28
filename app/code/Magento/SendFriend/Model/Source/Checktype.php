<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Send to a Friend Limit sending by Source
 */
namespace Magento\SendFriend\Model\Source;

class Checktype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve Check Type Option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\SendFriend\Helper\Data::CHECK_IP, 'label' => __('IP Address')],
            ['value' => \Magento\SendFriend\Helper\Data::CHECK_COOKIE, 'label' => __('Cookie (unsafe)')]
        ];
    }
}
