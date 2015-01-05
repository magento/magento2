<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GroupedProduct\Block\Product\Grouped;

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
     * @return string
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
}
