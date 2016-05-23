<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System;

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
            'Magento\Backend\Block\Widget\Button',
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
