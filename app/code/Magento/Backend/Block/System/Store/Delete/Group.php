<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store\Delete;

/**
 * Adminhtml store delete group block
 */
class Group extends \Magento\Backend\Block\Template
{
    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        $itemId = $this->getRequest()->getParam('group_id');

        $this->setTemplate('Magento_Backend::system/store/delete_group.phtml');
        $this->setAction($this->getUrl('adminhtml/*/deleteGroupPost', ['group_id' => $itemId]));
        $this->addChild(
            'confirm_deletion_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Delete Store'), 'onclick' => "deleteForm.submit()", 'class' => 'cancel']
        );
        $onClick = "setLocation('" . $this->getUrl('adminhtml/*/editGroup', ['group_id' => $itemId]) . "')";
        $this->addChild(
            'cancel_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Cancel'), 'onclick' => $onClick, 'class' => 'cancel']
        );
        $this->addChild(
            'back_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Back'), 'onclick' => $onClick, 'class' => 'cancel']
        );
        return parent::_prepareLayout();
    }
}
