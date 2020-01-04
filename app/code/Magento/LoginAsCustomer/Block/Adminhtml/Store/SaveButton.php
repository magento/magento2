<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomer\Block\Adminhtml\Store;

/**
 * Class SaveButton
 */
class SaveButton extends \Magento\Community\Block\Adminhtml\Edit\SaveButton
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Login As Customer'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
