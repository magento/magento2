<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Block\Adminhtml\Product;

/**
 * Adminhtml product downloads report
 */
class Downloads extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize Downloads
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_product_downloads';
        $this->_headerText = __('Downloads');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
