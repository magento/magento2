<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Shopcart;

/**
 * Adminhtml Shopping cart products report page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_shopcart_product';
        $this->_headerText = __('Products in carts');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
