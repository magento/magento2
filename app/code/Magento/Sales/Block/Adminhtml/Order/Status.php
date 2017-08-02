<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order;

/**
 * Adminhtml sales order's status management block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Class constructor
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCreateUrl()
    {
        return $this->getUrl('sales/order_status/new');
    }

    /**
     * Assign url getter
     *
     * @return string
     * @since 2.0.0
     */
    public function getAssignUrl()
    {
        return $this->getUrl('sales/order_status/assign');
    }
}
