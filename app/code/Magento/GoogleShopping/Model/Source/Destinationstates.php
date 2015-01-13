<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Source;

/**
 * Google Data Api destination states
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Destinationstates implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve option array with destinations
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\Framework\Gdata\Gshopping\Extension\Control::DEST_MODE_DEFAULT, 'label' => __('Default')],
            [
                'value' => \Magento\Framework\Gdata\Gshopping\Extension\Control::DEST_MODE_REQUIRED,
                'label' => __('Required')
            ],
            ['value' => \Magento\Framework\Gdata\Gshopping\Extension\Control::DEST_MODE_EXCLUDED, 'label' => __('Excluded')]
        ];
    }
}
