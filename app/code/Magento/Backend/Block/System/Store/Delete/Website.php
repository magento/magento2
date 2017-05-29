<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Delete;

/**
 * Adminhtml store delete group block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Website extends \Magento\Backend\Block\Template
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $itemId = $this->getRequest()->getParam('website_id');

        $this->setTemplate('system/store/delete_website.phtml');
        $this->setAction($this->getUrl('adminhtml/*/deleteWebsitePost', ['website_id' => $itemId]));
        $this->addChild(
            'confirm_deletion_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Delete Web Site'), 'onclick' => "deleteForm.submit()", 'class' => 'cancel']
        );
        $onClick = "setLocation('" . $this->getUrl('adminhtml/*/editWebsite', ['website_id' => $itemId]) . "')";
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
