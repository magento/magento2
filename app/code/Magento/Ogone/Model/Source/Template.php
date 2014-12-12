<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Ogone template Action Dropdown source
 */
namespace Magento\Ogone\Model\Source;

class Template implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Prepare ogone template mode list as option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\Ogone\Model\Api::TEMPLATE_OGONE, 'label' => __('Ogone')],
            ['value' => \Magento\Ogone\Model\Api::TEMPLATE_MAGENTO, 'label' => __('Magento')]
        ];
    }
}
