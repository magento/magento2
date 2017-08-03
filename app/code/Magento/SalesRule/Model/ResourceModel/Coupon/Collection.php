<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Coupon;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\SalesRule\Model\Rule;

/**
 * SalesRule Model Resource Coupon_Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends AbstractCollection
{
    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magento\SalesRule\Model\Coupon::class, \Magento\SalesRule\Model\ResourceModel\Coupon::class);
    }

    /**
     * Add rule to filter
     *
     * @param Rule|int $rule
     * @return $this
     * @since 2.0.0
     */
    public function addRuleToFilter($rule)
    {
        if ($rule instanceof Rule) {
            $ruleId = $rule->getId();
        } else {
            $ruleId = (int)$rule;
        }

        $this->addFieldToFilter('rule_id', $ruleId);

        return $this;
    }

    /**
     * Add rule IDs to filter
     *
     * @param array $ruleIds
     * @return $this
     * @since 2.0.0
     */
    public function addRuleIdsToFilter(array $ruleIds)
    {
        $this->addFieldToFilter('rule_id', ['in' => $ruleIds]);
        return $this;
    }

    /**
     * Filter collection to be filled with auto-generated coupons only
     *
     * @return $this
     * @since 2.0.0
     */
    public function addGeneratedCouponsFilter()
    {
        $this->addFieldToFilter('is_primary', ['null' => 1])->addFieldToFilter('type', '1');
        return $this;
    }

    /**
     * Callback function that filters collection by field "Used" from grid
     *
     * @param AbstractCollection $collection
     * @param Column $column
     * @return void
     * @since 2.0.0
     */
    public function addIsUsedFilterCallback($collection, $column)
    {
        $filterValue = $column->getFilter()->getCondition();

        $expression = $this->getConnection()->getCheckSql('main_table.times_used > 0', 1, 0);
        $conditionSql = $this->_getConditionSql($expression, $filterValue);
        $collection->getSelect()->where($conditionSql);
    }
}
