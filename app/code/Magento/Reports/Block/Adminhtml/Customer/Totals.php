<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Customer;

/**
 * Backend customers by totals report content block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Totals extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_Reports';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_customer_totals';
        $this->_headerText = __('Customers by Orders Total');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
