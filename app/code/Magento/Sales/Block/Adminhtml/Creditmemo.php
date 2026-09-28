<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Block\Adminhtml;

/**
 * Adminhtml sales creditmemos block
 */
class Creditmemo extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_creditmemo';
        $this->_blockGroup = 'Magento_Sales';
        $this->_headerText = __('Credit Memos');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
