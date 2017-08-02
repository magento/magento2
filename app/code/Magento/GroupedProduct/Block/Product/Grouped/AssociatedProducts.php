<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Block\Product\Grouped;

/**
 * @api
 * @since 2.0.0
 */
class AssociatedProducts extends \Magento\Backend\Block\Catalog\Product\Tab\Container
{
    /**
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return __('Grouped Products');
    }

    /**
     * Get parent tab code
     *
     * @return string
     * @since 2.0.0
     */
    public function getParentTab()
    {
        return 'product-details';
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->setData('opened', true);
        return $this;
    }
}
