<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Adminhtml\Attribute\Validation\Rules;

/**
 * Class Options
 * @since 2.1.0
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('None')],
            ['value' => 'validate-number', 'label' => __('Decimal Number')],
            ['value' => 'validate-digits', 'label' => __('Integer Number')],
            ['value' => 'validate-email', 'label' => __('Email')],
            ['value' => 'validate-url', 'label' => __('URL')],
            ['value' => 'validate-alpha', 'label' => __('Letters')],
            ['value' => 'validate-alphanum', 'label' => __('Letters (a-z, A-Z) or Numbers (0-9)')]
        ];
    }
}
