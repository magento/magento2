<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Grid;

use Magento\Framework\Search\AggregationInterface;

/**
 * Flat sales order grid collection
 */
class Collection extends \Magento\Sales\Model\Resource\Grid\Collection
{
    /**
     * Customer mode flag
     *
     * @var bool
     */
    protected $_customerModeFlag = false;

    /**
     * Get SQL for get record count
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        if ($this->getIsCustomerMode()) {
            $this->_renderFilters();

            $unionSelect = clone $this->getSelect();

            $unionSelect->reset(\Zend_Db_Select::ORDER);
            $unionSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
            $unionSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);

            $countSelect = clone $this->getSelect();
            $countSelect->reset();
            $countSelect->from(['a' => $unionSelect], 'COUNT(*)');
        } else {
            $countSelect = parent::getSelectCountSql();
        }

        return $countSelect;
    }

    /**
     * Set customer mode flag value
     *
     * @param bool $value
     * @return $this
     */
    public function setIsCustomerMode($value)
    {
        $this->_customerModeFlag = (bool)$value;
        return $this;
    }

    /**
     * Get customer mode flag value
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCustomerMode()
    {
        return $this->_customerModeFlag;
    }
}
