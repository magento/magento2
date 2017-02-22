<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit;

/**
 * Adminhtml product edit tabs
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tabs extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs
{
    /**
     * @var string
     */
    protected $_attributeTabBlock = 'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes';

    /**
     * @return $this|void
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
