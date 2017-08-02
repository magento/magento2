<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block\Adminhtml\Synonyms\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveButton
 * @since 2.1.0
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @since 2.1.0
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save Synonym Group'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
