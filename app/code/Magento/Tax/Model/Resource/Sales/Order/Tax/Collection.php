<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Resource\Sales\Order\Tax;

/**
 * Order Tax Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Tax\Model\Sales\Order\Tax', 'Magento\Tax\Model\Resource\Sales\Order\Tax');
    }

    /**
     * Retrieve order tax collection by order identifier
     *
     * @param \Magento\Framework\Object $order
     * @return \Magento\Tax\Model\Resource\Sales\Order\Tax\Collection
     */
    public function loadByOrder($order)
    {
        $orderId = $order->getId();
        $this->getSelect()->where('main_table.order_id = ?', (int)$orderId)->order('process');
        return $this->load();
    }
}
