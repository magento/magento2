<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml Google Contyent Item Types Grid
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Block\Adminhtml;

class Types extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_GoogleShopping';
        $this->_controller = 'adminhtml_types';
        $this->_addButtonLabel = __('Add Attribute Mapping');
        $this->_headerText = __('Manage Attribute Mapping');
        parent::_construct();
    }
}
