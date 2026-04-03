<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Tax report resource model with aggregation by updated at
 */
namespace Magento\Tax\Model\ResourceModel\Report\Tax;

class Updatedat extends \Magento\Tax\Model\ResourceModel\Report\Tax\Createdat
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tax_order_aggregated_updated', 'id');
    }

    /**
     * Aggregate Tax data by order updated at
     *
     * @param mixed $from
     * @param mixed $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByOrder('updated_at', $from, $to);
    }
}
