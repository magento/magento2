<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Widget Instance edit tabs container
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit;

/**
 * @api
 * @since 100.0.2
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('widget_instace_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Widget'));
    }
}
