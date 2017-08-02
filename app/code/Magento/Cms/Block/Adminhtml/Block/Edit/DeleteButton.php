<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml\Block\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @since 2.1.0
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @since 2.1.0
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getBlockId()) {
            $data = [
                'label' => __('Delete Block'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['block_id' => $this->getBlockId()]);
    }
}
