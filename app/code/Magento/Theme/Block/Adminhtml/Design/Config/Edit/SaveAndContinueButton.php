<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\Design\Config\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * "Save and continue" button data provider
 *
 * @api
 * @since 100.1.0
 */
class SaveAndContinueButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @since 100.1.0
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Continue'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinue'],
                ],
                'form-role' => 'saveAndContinue'
            ],
            'sort_order' => 15,
        ];
    }
}
