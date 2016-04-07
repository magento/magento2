<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Delete;

/**
 * Adminhtml store delete group block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Group extends \Magento\Backend\Block\Template
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $itemId = $this->getRequest()->getParam('group_id');

        $this->setTemplate('system/store/delete_group.phtml');
        $this->setAction($this->getUrl('adminhtml/*/deleteGroupPost', ['group_id' => $itemId]));
        $this->addChild(
            'confirm_deletion_button',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Delete Store'), 'onclick' => "deleteForm.submit()", 'class' => 'cancel']
        );
        $onClick = "setLocation('" . $this->getUrl('adminhtml/*/editGroup', ['group_id' => $itemId]) . "')";
        $this->addChild(
            'cancel_button',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Cancel'), 'onclick' => $onClick, 'class' => 'cancel']
        );
        $this->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Back'), 'onclick' => $onClick, 'class' => 'cancel']
        );
        return parent::_prepareLayout();
    }
}
