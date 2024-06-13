<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Block\Adminhtml\Shopcart;

/**
 * Adminhtml Shopping cart products report page content block
 */
class Product extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_shopcart_product';
        $this->_headerText = __('Products in carts');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
