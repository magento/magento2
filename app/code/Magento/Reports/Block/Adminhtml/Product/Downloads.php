<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Product;

/**
 * Adminhtml product downloads report
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Downloads extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_product_downloads';
        $this->_headerText = __('Downloads');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
