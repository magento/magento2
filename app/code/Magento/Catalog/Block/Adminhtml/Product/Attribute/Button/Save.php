<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Button;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Product\Attribute\Button\Save
 *
 * @since 2.1.0
 */
class Save extends Generic
{
    /**
     * Get button data
     *
     * @return array
     * @since 2.1.0
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save Attribute'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ]
        ];
    }
}
