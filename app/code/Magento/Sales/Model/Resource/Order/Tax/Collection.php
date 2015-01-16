<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Tax;

/**
 * Order Tax Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Order\Tax', 'Magento\Sales\Model\Resource\Order\Tax');
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
