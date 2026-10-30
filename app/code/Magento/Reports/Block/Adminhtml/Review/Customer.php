<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Block\Adminhtml\Review;

/**
 * Adminhtml cms blocks content block
 *
 * @api
 * @since 100.0.2
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
        $this->_controller = 'adminhtml_review_customer';
        $this->_headerText = __('Customers Reviews');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
