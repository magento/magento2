<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Product;

/**
 * Backend Report Sold Product Content Block
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Sold extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_blockGroup = 'Magento_Reports';

    /**
     * Initialize container block settings
     *
     * @return void
     * @since 2.0.0
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
