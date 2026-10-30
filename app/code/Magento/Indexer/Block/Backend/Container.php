<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Indexer\Block\Backend;

/**
 * @api
 * @since 100.0.2
 */
class Container extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize object state with incoming parameters
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'indexer';
        $this->_blockGroup = 'Magento_Indexer';
        $this->_headerText = __('Indexer Management');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
