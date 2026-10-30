<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit;

/**
 * Adminhtml product edit tabs
 */
class Tabs extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs
{
    /**
     * @var string
     */
    protected $_attributeTabBlock = \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes::class;

    /**
     * Prepare the layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->addTab(
            'bundle_items',
            [
                'label' => __('Bundle Items'),
                'url' => $this->getUrl('adminhtml/*/bundles', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->bindShadowTabs('bundle_items', 'customer_options');
    }
}
