<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Report\Rule;

/**
 * Rule report resource model with aggregation by updated at
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Updatedat extends \Magento\SalesRule\Model\ResourceModel\Report\Rule\Createdat
{
    /**
     * Resource Report Rule constructor
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByOrder('updated_at', $from, $to);
    }
}
