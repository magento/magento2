<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Used in creating options for Yes|No|Specified config value selection
 *
 */
namespace Magento\Backend\Model\Config\Source;

class Yesnocustom implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 0, 'label' => __('No')],
            ['value' => 2, 'label' => __('Specified')]
        ];
    }
}
