<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Review;

/**
 * Adminhtml cms blocks content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Customer extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_review_customer';
        $this->_headerText = __('Customers Reviews');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
