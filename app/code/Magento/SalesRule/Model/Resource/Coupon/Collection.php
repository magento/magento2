<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_SalesRule
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * SalesRule Model Resource Coupon_Collection
 *
 * @category    Magento
 * @package     Magento_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\SalesRule\Model\Resource\Coupon;

class Collection extends \Magento\Core\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\SalesRule\Model\Coupon', 'Magento\SalesRule\Model\Resource\Coupon');
    }

    /**
     * Add rule to filter
     *
     * @param \Magento\SalesRule\Model\Rule|int $rule
     *
     * @return \Magento\SalesRule\Model\Resource\Coupon\Collection
     */
    public function addRuleToFilter($rule)
    {
        if ($rule instanceof \Magento\SalesRule\Model\Rule) {
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
     *
     * @return \Magento\SalesRule\Model\Resource\Coupon\Collection
     */
    public function addRuleIdsToFilter(array $ruleIds)
    {
        $this->addFieldToFilter('rule_id', array('in' => $ruleIds));
        return $this;
    }

    /**
     * Filter collection to be filled with auto-generated coupons only
     *
     * @return \Magento\SalesRule\Model\Resource\Coupon\Collection
     */
    public function addGeneratedCouponsFilter()
    {
        $this->addFieldToFilter('is_primary', array('null' => 1))->addFieldToFilter('type', '1');
        return $this;
    }

    /**
     * Callback function that filters collection by field "Used" from grid
     *
     * @param \Magento\Core\Model\Resource\Db\Collection\AbstractCollection $collection
     * @param \Magento\Adminhtml\Block\Widget\Grid\Column $column
     */
    public function addIsUsedFilterCallback($collection, $column)
    {
        $filterValue = $column->getFilter()->getCondition();

        $expression = $this->getConnection()->getCheckSql('main_table.times_used > 0', 1, 0);
        $conditionSql = $this->_getConditionSql($expression, $filterValue);
        $collection->getSelect()->where($conditionSql);
    }
}
