<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml;

/**
 * Adminhtml cms blocks content block
 * @since 2.0.0
 */
class Block extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Cms';
        $this->_controller = 'adminhtml_block';
        $this->_headerText = __('Static Blocks');
        $this->_addButtonLabel = __('Add New Block');
        parent::_construct();
    }
}
