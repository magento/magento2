<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Collection;

/**
 * Flat sales order collection
 */
abstract class AbstractCollection extends \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * Order object
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_salesOrder = null;

    /**
     * Order field for setOrderFilter
     *
     * @var string
     */
    protected $_orderField = 'parent_id';

    /**
     * Set sales order model as parent collection object
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setSalesOrder($order)
    {
        $this->_salesOrder = $order;
        if ($this->_eventPrefix && $this->_eventObject) {
            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_set_sales_order',
                ['collection' => $this, $this->_eventObject => $this, 'order' => $order]
            );
        }

        return $this;
    }

    /**
     * Retrieve sales order as parent collection object
     *
     * @return \Magento\Sales\Model\Order|null
     */
    public function getSalesOrder()
    {
        return $this->_salesOrder;
    }

    /**
     * Add order filter
     *
     * @param int|\Magento\Sales\Model\Order|array $order
     * @return $this
     */
    public function setOrderFilter($order)
    {
        if ($order instanceof \Magento\Sales\Model\Order) {
            $this->setSalesOrder($order);
            $orderId = $order->getId();
            if ($orderId) {
                $this->addFieldToFilter($this->_orderField, $orderId);
            } else {
                // An order without an ID has no related entities. Filter on an ID that can never match, so that
                // resetting the loaded state (clear(), _reset()) cannot turn this into an unfiltered table scan.
                $this->addFieldToFilter($this->_orderField, 0);
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter($this->_orderField, $order);
        }
        return $this;
    }
}
