<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo;

/**
 * Catalog price rules
 *
 * @api
 * @since 100.0.2
 */
class Quote extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'promo_quote';
        $this->_headerText = __('Cart Price Rules');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
    }
}
