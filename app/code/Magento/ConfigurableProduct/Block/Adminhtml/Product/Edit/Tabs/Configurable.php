<?php
/**
 * Edit tabs for configurable products
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tabs;

class Configurable extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs
{
    /**
     * Preparing layout
     *
     * @return \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tabs\Configurable
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'super_settings',
            [
                'label' => __('Configurable Product Settings'),
                'content' => $this->getLayout()->createBlock(
                    'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Settings'
                )->toHtml(),
                'active' => true
            ]
        );
    }
}
