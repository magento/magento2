<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Block\Adminhtml\Shopcart;

/**
 * Adminhtml Shopping cart customers report page content block
 */
class Customer extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_shopcart_customer';
        $this->_headerText = __('Customers');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
