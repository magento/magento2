<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Block\Adminhtml;

/**
 * Adminhtml sales shipments block
 */
class Shipment extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_shipment';
        $this->_blockGroup = 'Magento_Sales';
        $this->_headerText = __('Shipments');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
