<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cms\Block\Adminhtml\Page\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getPageId()) {
            $confirmMessage = $this->context->getEscaper()->escapeJs(
                $this->context->getEscaper()->escapeHtml(__('Are you sure you want to do this?'))
            );
            $data = [
                'label' => __('Delete Page'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . $confirmMessage . '\', \''
                    . $this->getDeleteUrl() . '\', {"data": {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Url to send delete requests to.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['page_id' => $this->getPageId()]);
    }
}
