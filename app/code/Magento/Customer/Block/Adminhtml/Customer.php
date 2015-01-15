<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml customers list block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Adminhtml;

class Customer extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Magento_Customer';
        $this->_headerText = __('Customers');
        $this->_addButtonLabel = __('Add New Customer');
        parent::_construct();
    }
}
