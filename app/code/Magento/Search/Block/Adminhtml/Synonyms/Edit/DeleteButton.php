<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block\Adminhtml\Synonyms\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Delete Synonyms Group Button Class
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Delete Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getGroupId()) {
            $data = [
                'label' => __('Delete Synonym Group'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\''
                    . __('Are you sure you want to delete this synonym group?')
                    . '\', \'' . $this->getDeleteUrl() . '\', {data: {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Delete Url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['group_id' => $this->getGroupId()]);
    }
}
