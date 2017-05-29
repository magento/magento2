<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Block\Backend;

/**
 * @api
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
