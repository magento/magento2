<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Report\Order;

/**
 * Order entity resource model with aggregation by updated at
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Updatedat extends Createdat
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_aggregated_updated', 'id');
    }

    /**
     * Aggregate Orders data by order updated at
     *
     * @param string|int|\Zend_Date|array|null $from
     * @param string|int|\Zend_Date|array|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByField('updated_at', $from, $to);
    }
}
