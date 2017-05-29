<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Block\Product\Grouped;

/**
 * @api
 */
class AssociatedProducts extends \Magento\Backend\Block\Catalog\Product\Tab\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grouped_product_container');
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Grouped Products');
    }

    /**
     * Get parent tab code
     *
     * @return string
     */
    public function getParentTab()
    {
        return 'product-details';
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setData('opened', true);
        return $this;
    }
}
