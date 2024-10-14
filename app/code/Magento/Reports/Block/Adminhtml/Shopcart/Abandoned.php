<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Shopcart;

/**
 * Adminhtml abandoned shopping cart report page content block
 */
class Abandoned extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_shopcart_abandoned';
        $this->_headerText = __('Abandoned carts');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
