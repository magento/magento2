<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Ogone template Action Dropdown source
 */
namespace Magento\Ogone\Model\Source;

class Pmlist implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Prepare ogone payment block layout as option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\Ogone\Model\Api::PMLIST_HORISONTAL_LEFT,
                'label' => __('Horizontally grouped logo with group name on left'),
            ],
            [
                'value' => \Magento\Ogone\Model\Api::PMLIST_HORISONTAL,
                'label' => __('Horizontally grouped logo with no group name')
            ],
            ['value' => \Magento\Ogone\Model\Api::PMLIST_VERTICAL, 'label' => __('Verical list')]
        ];
    }
}
