<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml;

/**
 * Adminhtml sales orders block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Order extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_order';
        $this->_blockGroup = 'Magento_Sales';
        $this->_headerText = __('Orders');
        $this->_addButtonLabel = __('Create New Order');
        parent::_construct();
        if (!$this->_authorization->isAllowed('Magento_Sales::create')) {
            $this->buttonList->remove('add');
        }
    }

    /**
     * Retrieve url for order creation
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('sales/order_create/start');
    }
}
