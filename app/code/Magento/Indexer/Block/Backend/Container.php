<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Block\Backend;

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
