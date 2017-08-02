<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Order;

/**
 * Order entity resource model with aggregation by updated at
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Updatedat extends Createdat
{
    /**
     * Model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('sales_order_aggregated_updated', 'id');
    }

    /**
     * Aggregate Orders data by order updated at
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     * @since 2.0.0
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByField('updated_at', $from, $to);
    }
}
