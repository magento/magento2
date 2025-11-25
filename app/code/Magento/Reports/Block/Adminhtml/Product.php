<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Block\Adminhtml;

/**
 * Adminhtml products report page content block
 */
class Product extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_product';
        $this->_headerText = __('Products Report');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
