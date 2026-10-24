<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Widget Instance grid container
 */
namespace Magento\Widget\Block\Adminhtml\Widget;

/**
 * @api
 * @since 100.0.2
 */
class Instance extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Widget';
        $this->_controller = 'adminhtml_widget_instance';
        $this->_headerText = __('Manage Widget Instances');
        parent::_construct();
        $this->buttonList->update('add', 'label', __('Add Widget'));
    }
}
