<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System;

/**
 * @api
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
