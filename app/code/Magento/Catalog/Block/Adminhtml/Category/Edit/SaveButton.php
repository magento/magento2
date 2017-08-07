<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;

/**
 * Class SaveButton
 * @since 2.1.0
 */
class SaveButton extends AbstractCategory implements ButtonProviderInterface
{
    /**
     * Save button
     *
     * @return array
     * @since 2.1.0
     */
    public function getButtonData()
    {
        $category = $this->getCategory();

        if (!$category->isReadonly() && $this->hasStoreRootCategory()) {
            return [
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save']],
                    'form-role' => 'save',
                ],
                'sort_order' => 30,
            ];
        }

        return [];
    }
}
