<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Block\Adminhtml\Customer;

/**
 * Backend customers by totals report content block
 *
 * @api
 * @since 100.0.2
 */
class Totals extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_Reports';

    /**
     * Initialize Totals
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_customer_totals';
        $this->_headerText = __('Customers by Orders Total');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
