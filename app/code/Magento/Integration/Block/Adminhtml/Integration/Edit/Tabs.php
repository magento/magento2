<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Integration\Block\Adminhtml\Integration\Edit;

/**
 * @api
 * @since 100.0.2
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Initialize integration edit page tabs
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('integration_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Basic Settings'));
    }
}
