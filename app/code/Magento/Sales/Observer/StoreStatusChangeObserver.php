<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\InsertOrderStatusChangeHistory;

class StoreStatusChangeObserver implements ObserverInterface
{
    /**
     * @param InsertOrderStatusChangeHistory $salesOrderStatusChangeHistory
     */
    public function __construct(
        private readonly InsertOrderStatusChangeHistory $salesOrderStatusChangeHistory,
    ) {
    }

    /**
     * Store status in sales_order_status_change_history table if the status is updated
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        //Insert order status into sales_order_status_change_history table if the order status is changed
        $this->salesOrderStatusChangeHistory->execute($order);
        return $this;
    }
}
