<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Block\Adminhtml\Product;

/**
 * Adminhtml catalog product attributes block
 */
class Attribute extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialise the block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_product_attribute';
        $this->_blockGroup = 'Magento_Catalog';
        $this->_headerText = __('Product Attributes');
        $this->_addButtonLabel = __('Add New Attribute');
        parent::_construct();
    }
}
