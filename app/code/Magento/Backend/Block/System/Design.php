<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System;

/**
 * @api
 * @since 100.0.2
 */
class Design extends \Magento\Backend\Block\Template
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('Magento_Backend::system/design/index.phtml');

        $this->getToolbar()->addChild(
            'add_new_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Add Design Change'),
                'onclick' => "setLocation('" . $this->getUrl('adminhtml/*/new') . "')",
                'class' => 'add primary add-design-change'
            ]
        );

        $this->getLayout()->getBlock('page.title')->setPageTitle('Store Design Schedule');

        return parent::_prepareLayout();
    }
}
