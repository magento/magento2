<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report collection abstract model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Report\Collection;

class AbstractCollection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * From date
     *
     * @var string
     */
    protected $_from = null;

    /**
     * To date
     *
     * @var string
     */
    protected $_to = null;

    /**
     * Period
     *
     * @var string
     */
    protected $_period = null;

    /**
     * Store ids
     *
     * @var int|array
     */
    protected $_storesIds = 0;

    /**
     * Is totals
     *
     * @var bool
     */
    protected $_isTotals = false;

    /**
     * Is subtotals
     *
     * @var bool
     */
    protected $_isSubTotals = false;

    /**
     * Aggregated columns
     *
     * @var array
     */
    protected $_aggregatedColumns = [];

    /**
     * Set array of columns that should be aggregated
     *
     * @param array $columns
     * @return $this
     */
    public function setAggregatedColumns(array $columns)
    {
        $this->_aggregatedColumns = $columns;
        return $this;
    }

    /**
     * Retrieve array of columns that should be aggregated
     *
     * @return array
     */
    public function getAggregatedColumns()
    {
        return $this->_aggregatedColumns;
    }

    /**
     * Set date range
     *
     * @param mixed $from
     * @param mixed $to
     * @return $this
     */
    public function setDateRange($from = null, $to = null)
    {
        $this->_from = $from;
        $this->_to = $to;
        return $this;
    }

    /**
     * Set period
     *
     * @param string $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->_period = $period;
        return $this;
    }

    /**
     * Apply needed aggregated table
     *
     * @return $this
     */
    protected function _applyAggregatedTable()
    {
        return $this;
    }

    /**
     * Apply date range filter
     *
     * @return $this
     */
    protected function _applyDateRangeFilter()
    {
        // Remember that field PERIOD is a DATE(YYYY-MM-DD) in all databases
        if ($this->_from !== null) {
            $this->getSelect()->where('period >= ?', $this->_from);
        }
        if ($this->_to !== null) {
            $this->getSelect()->where('period <= ?', $this->_to);
        }

        return $this;
    }

    /**
     * Set store ids
     *
     * @param mixed $storeIds (null, int|string, array, array may contain null)
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $this->_storesIds = $storeIds;
        return $this;
    }

    /**
     * Apply stores filter to select object
     *
     * @param \Zend_Db_Select $select
     * @return $this
     */
    protected function _applyStoresFilterToSelect(\Zend_Db_Select $select)
    {
        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        if ($nullCheck) {
            $select->where('store_id IN(?) OR store_id IS NULL', $storeIds);
        } else {
            $select->where('store_id IN(?)', $storeIds);
        }

        return $this;
    }

    /**
     * Apply stores filter
     *
     * @return $this
     */
    protected function _applyStoresFilter()
    {
        return $this->_applyStoresFilterToSelect($this->getSelect());
    }

    /**
     * Getter/Setter for isTotals
     *
     * @param null|bool $flag
     * @return $this
     */
    public function isTotals($flag = null)
    {
        if (is_null($flag)) {
            return $this->_isTotals;
        }
        $this->_isTotals = $flag;
        return $this;
    }

    /**
     * Getter for isSubTotals
     *
     * @return bool
     */
    public function isSubTotals()
    {
        return $this->_isSubTotals;
    }

    /**
     * Setter for isSubTotals
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsSubTotals($flag)
    {
        $this->_isSubTotals = $flag;
        return $this;
    }

    /**
     * Custom filters application ability
     *
     * @return $this
     */
    protected function _applyCustomFilter()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        return $this;
    }

    /**
     * Apply filters common to reports
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        $this->_applyAggregatedTable();
        $this->_applyDateRangeFilter();
        $this->_applyStoresFilter();
        $this->_applyCustomFilter();
        return $this;
    }
}
