<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Newsletter;

/**
 * Adminhtml sales order create newsletter form block
 *
 * @api
 * @since 100.0.2
 */
class Form extends \Magento\Backend\Block\Widget
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_newsletter_form');
    }
}
