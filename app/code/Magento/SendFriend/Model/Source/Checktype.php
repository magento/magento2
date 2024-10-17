<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Send to a Friend Limit sending by Source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\SendFriend\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\SendFriend\Helper\Data as SendFriendHelper;

class Checktype implements ArrayInterface
{
    /**
     * Retrieve Check Type Option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => SendFriendHelper::CHECK_IP, 'label' => __('IP Address')],
            ['value' => SendFriendHelper::CHECK_COOKIE, 'label' => __('Cookie (unsafe)')]
        ];
    }
}
