<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * New Accounts Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Accounts;

class Collection extends \Magento\Reports\Model\Resource\Customer\Collection
{
    /**
     * Join created_at and accounts fields
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    protected function _joinFields($fromDate = '', $toDate = '')
    {
        $this->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $this->addAttributeToFilter(
            'created_at',
            ['from' => $fromDate, 'to' => $toDate, 'datetime' => true]
        )->addExpressionAttributeToSelect(
            'accounts',
            'COUNT({{entity_id}})',
            ['entity_id']
        );

        $this->getSelect()->having("{$this->_joinFields['accounts']['field']} > ?", 0);

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
     * Set store ids to final result
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->addAttributeToFilter('store_id', ['in' => (array)$storeIds]);
        }
        return $this;
    }
}
