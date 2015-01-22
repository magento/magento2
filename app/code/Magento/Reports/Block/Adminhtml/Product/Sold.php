<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Product;

/**
 * Backend Report Sold Product Content Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Sold extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_Reports';

    /**
     * Initialize container block settings
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_product_sold';
        $this->_headerText = __('Products Ordered');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
