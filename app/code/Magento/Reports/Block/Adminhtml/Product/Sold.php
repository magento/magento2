<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Block\Adminhtml\Product;

/**
 * Backend Report Sold Product Content Block
 *
 * @api
 * @since 100.0.2
 */
class Sold extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_Reports';

    /**
     * Initialize container block settings
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_product_sold';
        $this->_headerText = __('Products Ordered');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
