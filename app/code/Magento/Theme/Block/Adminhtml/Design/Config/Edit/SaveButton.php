<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\Design\Config\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * "Save" button data provider
 *
 * @api
 * @since 2.1.0
 */
class SaveButton implements ButtonProviderInterface
{
    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save Configuration'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 20,
        ];
    }
}
