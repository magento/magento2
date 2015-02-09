<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customers by totals Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Customer\Totals;

class Collection extends \Magento\Reports\Model\Resource\Order\Collection
{
    /**
     * Join fields
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    protected function _joinFields($fromDate = '', $toDate = '')
    {
        $this->joinCustomerName()->groupByCustomer()->addOrdersCount()->addAttributeToFilter(
            'created_at',
            ['from' => $fromDate, 'to' => $toDate, 'datetime' => true]
        );
        return $this;
    }

    /**
     * Set date range
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    public function setDateRange($fromDate, $toDate)
    {
        $this->_reset()->_joinFields($fromDate, $toDate);
        return $this;
    }

    /**
     * Set store filter collection
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->addAttributeToFilter('store_id', ['in' => (array)$storeIds]);
            $this->addSumAvgTotals(1)->orderByTotalAmount();
        } else {
            $this->addSumAvgTotals()->orderByTotalAmount();
        }

        return $this;
    }
}
