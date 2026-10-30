<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Review\Block\Adminhtml;

/**
 * Ratings grid
 *
 * @api
 * @since 100.0.2
 */
class Rating extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialise the block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Magento_Review';
        $this->_headerText = __('Manage Ratings');
        $this->_addButtonLabel = __('Add New Rating');
        parent::_construct();
    }
}
