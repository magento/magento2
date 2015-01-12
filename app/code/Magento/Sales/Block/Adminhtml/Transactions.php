<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml;

/**
 * Adminhtml sales transactions block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Transactions extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_transactions';
        $this->_blockGroup = 'Magento_Sales';
        $this->_headerText = __('Transactions');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
