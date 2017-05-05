<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Product;

/**
 * Adminhtml low stock products report content block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Lowstock extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_product_lowstock';
        $this->_headerText = __('Low stock');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
