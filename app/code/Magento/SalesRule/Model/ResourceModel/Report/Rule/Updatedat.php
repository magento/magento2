<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\ResourceModel\Report\Rule;

/**
 * Rule report resource model with aggregation by updated at
 */
class Updatedat extends \Magento\SalesRule\Model\ResourceModel\Report\Rule\Createdat
{
    /**
     * Resource Report Rule constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('salesrule_coupon_aggregated_updated', 'id');
    }

    /**
     * Aggregate Coupons data by order updated at
     *
     * @param mixed|null $from
     * @param mixed|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByOrder('updated_at', $from, $to);
    }
}
