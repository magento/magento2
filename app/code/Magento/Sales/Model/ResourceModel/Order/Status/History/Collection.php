<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Status\History;

use Magento\Sales\Api\Data\OrderStatusHistorySearchResultInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;

/**
 * Flat sales order status history collection
 */
class Collection extends AbstractCollection implements OrderStatusHistorySearchResultInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_status_history_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'order_status_history_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Sales\Model\Order\Status\History::class,
            \Magento\Sales\Model\ResourceModel\Order\Status\History::class
        );
    }

    /**
     * Get history object collection for specified instance (order, shipment, invoice or credit memo)
     * Parameter instance may be one of the following types: \Magento\Sales\Model\Order,
     * \Magento\Sales\Model\Order\Creditmemo, \Magento\Sales\Model\Order\Invoice, \Magento\Sales\Model\Order\Shipment
     *
     * @param AbstractModel $instance
     * @return \Magento\Sales\Model\Order\Status\History|null
     */
    public function getUnnotifiedForInstance($instance)
    {
        if (!$instance instanceof Order) {
            $instance = $instance->getOrder();
        }
        $this->setOrderFilter(
            $instance
        )->setOrder(
            'created_at',
            'desc'
        )->addFieldToFilter(
            'entity_name',
            $instance->getEntityType()
        )->addFieldToFilter(
            'is_customer_notified',
            0
        )->setPageSize(
            1
        );
        foreach ($this->getItems() as $historyItem) {
            return $historyItem;
        }
        return null;
    }
}
