<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdminNotification\Block;

/**
 * @api
 * @since 100.0.2
 */
class Inbox extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Magento_AdminNotification';
        $this->_headerText = __('Messages Inbox');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
