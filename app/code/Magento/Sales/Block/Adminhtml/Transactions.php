<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Block\Adminhtml;

/**
 * Adminhtml sales transactions block
 *
 * @api
 * @since 100.0.2
 */
class Transactions extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_transactions';
        $this->_blockGroup = 'Magento_Sales';
        $this->_headerText = __('Transactions');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
