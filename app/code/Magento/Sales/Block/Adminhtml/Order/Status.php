<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order;

/**
 * Adminhtml sales order's status management block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_order_status';
        $this->_headerText = __('Order Statuses');
        $this->_addButtonLabel = __('Create New Status');
        $this->buttonList->add(
            'assign',
            [
                'label' => __('Assign Status to State'),
                'onclick' => 'setLocation(\'' . $this->getAssignUrl() . '\')',
                'class' => 'add'
            ]
        );
        parent::_construct();
    }

    /**
     * Create url getter
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('sales/order_status/new');
    }

    /**
     * Assign url getter
     *
     * @return string
     */
    public function getAssignUrl()
    {
        return $this->getUrl('sales/order_status/assign');
    }
}
