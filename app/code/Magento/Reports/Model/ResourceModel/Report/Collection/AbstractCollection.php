<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel\Report\Collection;

/**
 * Report collection abstract model
 *
 * @api
 * @since 2.0.0
 */
class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * From date
     *
     * @var string
     * @since 2.0.0
     */
    protected $_from = null;

    /**
     * To date
     *
     * @var string
     * @since 2.0.0
     */
    protected $_to = null;

    /**
     * Period
     *
     * @var string
     * @since 2.0.0
     */
    protected $_period = null;

    /**
     * Store ids
     *
     * @var int|array
     * @since 2.0.0
     */
    protected $_storesIds = 0;

    /**
     * Is totals
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isTotals = false;

    /**
     * Is subtotals
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isSubTotals = false;

    /**
     * Aggregated columns
     *
     * @var array
     * @since 2.0.0
     */
    protected $_aggregatedColumns = [];

    /**
     * Set array of columns that should be aggregated
     * @codeCoverageIgnore
     *
     * @param array $columns
     * @return $this
     * @since 2.0.0
     */
    public function setAggregatedColumns(array $columns)
    {
        $this->_aggregatedColumns = $columns;
        return $this;
    }

    /**
     * Retrieve array of columns that should be aggregated
     * @codeCoverageIgnore
     *
     * @return array
     * @since 2.0.0
     */
    public function getAggregatedColumns()
    {
        return $this->_aggregatedColumns;
    }

    /**
     * Set date range
     * @codeCoverageIgnore
     *
     * @param mixed $from
     * @param mixed $to
     * @return $this
     * @since 2.0.0
     */
    public function setDateRange($from = null, $to = null)
    {
        $this->_from = $from;
        $this->_to = $to;
        return $this;
    }

    /**
     * Set period
     * @codeCoverageIgnore
     *
     * @param string $period
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _applyAggregatedTable()
    {
        return $this;
    }

    /**
     * Apply date range filter
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function addStoreFilter($storeIds)
    {
        $this->_storesIds = $storeIds;
        return $this;
    }

    /**
     * Apply stores filter to select object
     *
     * @param \Magento\Framework\DB\Select $select
     * @return $this
     * @since 2.0.0
     */
    protected function _applyStoresFilterToSelect(\Magento\Framework\DB\Select $select)
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isTotals($flag = null)
    {
        if ($flag === null) {
            return $this->_isTotals;
        }
        $this->_isTotals = $flag;
        return $this;
    }

    /**
     * Getter for isSubTotals
     *
     * @return bool
     * @since 2.0.0
     */
    public function isSubTotals()
    {
        return $this->_isSubTotals;
    }

    /**
     * Setter for isSubTotals
     * @codeCoverageIgnore
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _applyCustomFilter()
    {
        return $this;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        return $this;
    }

    /**
     * Apply filters common to reports
     *
     * @return $this
     * @since 2.0.0
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
