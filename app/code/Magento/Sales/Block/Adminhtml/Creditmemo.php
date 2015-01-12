<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml;

/**
 * Adminhtml sales creditmemos block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Creditmemo extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_creditmemo';
        $this->_blockGroup = 'Magento_Sales';
        $this->_headerText = __('Credit Memos');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
