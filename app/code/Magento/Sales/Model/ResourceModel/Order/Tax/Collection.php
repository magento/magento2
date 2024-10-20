<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Tax;

/**
 * Order Tax Collection
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\Order\Tax::class, \Magento\Sales\Model\ResourceModel\Order\Tax::class);
    }

    /**
     * Load by order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function loadByOrder($order)
    {
        $orderId = $order->getId();
        $this->getSelect()->where('main_table.order_id = ?', $orderId)->order('process');
        return $this->load();
    }
}
