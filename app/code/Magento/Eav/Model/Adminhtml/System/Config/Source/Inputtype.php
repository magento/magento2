<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
            ['value' => 'texteditor', 'label' => __('Text Editor')],
            ['value' => 'date', 'label' => __('Date')],
            ['value' => 'boolean', 'label' => __('Yes/No')],
            ['value' => 'multiselect', 'label' => __('Multiple Select')],
            ['value' => 'select', 'label' => __('Dropdown')]
        ];
    }

    /**
     * Get volatile input types.
     *
     * @return array
     */
    public function getVolatileInputTypes()
    {
        return [
            ['textarea', 'texteditor'],
        ];
    }

    /**
     * Get hint for input types
     *
     * @return array
     */
    public function getInputTypeHints()
    {
        return [
            'texteditor' => __(
                'Text Editor input type requires WYSIWYG to be enabled in Stores > Configuration > Content Management.'
            ),
        ];
    }
}
