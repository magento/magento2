<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Send to a Friend Limit sending by Source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
