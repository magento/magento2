<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Adminhtml\System\Config\Source;

class Inputtype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'text', 'label' => __('Text Field')],
            ['value' => 'textarea', 'label' => __('Text Area')],
            ['value' => 'date', 'label' => __('Date')],
            ['value' => 'boolean', 'label' => __('Yes/No')],
            ['value' => 'multiselect', 'label' => __('Multiple Select')],
            ['value' => 'select', 'label' => __('Dropdown')]
        ];
    }
}
