<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\ResourceModel\Sales\Order\Tax;

use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax as ResourceSalesOrderTax;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\Collection as SalesOrderTaxCollection;
use Magento\Tax\Model\Sales\Order\Tax as ModelSalesOrderTax;

/**
 * Order Tax Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ModelSalesOrderTax::class,
            ResourceSalesOrderTax::class
        );
    }

    /**
     * Retrieve order tax collection by order identifier
     *
     * @param DataObject $order
     * @return SalesOrderTaxCollection
     */
    public function loadByOrder($order)
    {
        $orderId = $order->getId();
        $this->getSelect()->where('main_table.order_id = ?', (int)$orderId)->order('process');
        return $this->load();
    }
}
