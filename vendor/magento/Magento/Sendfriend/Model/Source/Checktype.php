<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Send to a Friend Limit sending by Source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sendfriend\Model\Source;

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
            ['value' => \Magento\Sendfriend\Helper\Data::CHECK_IP, 'label' => __('IP Address')],
            ['value' => \Magento\Sendfriend\Helper\Data::CHECK_COOKIE, 'label' => __('Cookie (unsafe)')]
        ];
    }
}
