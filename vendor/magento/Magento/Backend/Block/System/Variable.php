<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Block\System;

/**
 * Custom Variable Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Variable extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Backend';
        $this->_controller = 'system_variable';
        $this->_headerText = __('Custom Variables');
        parent::_construct();
        $this->buttonList->update('add', 'label', __('Add New Variable'));
    }
}
