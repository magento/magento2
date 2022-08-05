<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report Products Review collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Review\Product;

use Magento\Framework\DB\Select;

/**
 * Review collection class
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Init Select
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinReview();
        return $this;
    }

    /**
     * Join review table to result
     *
     * @return $this
     */
    protected function _joinReview()
    {
        $subSelect = clone $this->getSelect();
        $subSelect->reset()->from(
            ['rev' => $this->getTable('review')],
            'COUNT(DISTINCT rev.review_id)'
        )->where(
            'e.entity_id = rev.entity_pk_value'
        );

        $this->addAttributeToSelect('name');

        $this->getSelect()->join(
            ['r' => $this->getTable('review')],
            'e.entity_id = r.entity_pk_value',
            [
                'review_cnt' => new \Zend_Db_Expr(sprintf('(%s)', $subSelect)),
                'last_review' => 'MAX(r.created_at)'
            ]
        )->group(
            'e.entity_id'
        );

        $joinCondition = [
            'e.entity_id = table_rating.entity_pk_value',
            $this->getConnection()->quoteInto('table_rating.store_id > ?', 0),
        ];

        $sumPercentField = new \Zend_Db_Expr("SUM(table_rating.percent)");
        $sumPercentApproved = new \Zend_Db_Expr('SUM(table_rating.percent_approved)');
        $countRatingId = new \Zend_Db_Expr('COUNT(table_rating.rating_id)');

        $this->getSelect()->joinLeft(
            ['table_rating' => $this->getTable('rating_option_vote_aggregated')],
            implode(' AND ', $joinCondition),
            [
                'avg_rating' => new \Zend_Db_Expr(sprintf('%s/%s', $sumPercentField, $countRatingId)),
                'avg_rating_approved' => new \Zend_Db_Expr(sprintf('%s/%s', $sumPercentApproved, $countRatingId))
            ]
        );

        return $this;
    }

    /**
     * Add attribute to sort
     *
     * @param string $attribute
     * @param string $dir
     * @return $this|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (in_array($attribute, ['review_cnt', 'last_review', 'avg_rating', 'avg_rating_approved'])) {
            $this->getSelect()->order($attribute . ' ' . $dir);
            return $this;
        }

        return parent::addAttributeToSort($attribute, $dir);
    }

    /**
     * Add attribute to filter
     *
     * @param string $attribute
     * @param array|null $condition
     * @param string $joinType
     * @return $this|\Magento\Catalog\Model\ResourceModel\Product\Collection
     * @since 100.3.5
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        $aggregateFilterArray = ['review_cnt', 'last_review', 'avg_rating', 'avg_rating_approved'];
        if (is_string($attribute) &&
            in_array($attribute, $aggregateFilterArray)) {
            $conditionSql = $this->_getConditionSql($attribute, $condition);
            $this->getSelect()->having($conditionSql, null, Select::TYPE_CONDITION);
        }
        return in_array($attribute, $aggregateFilterArray) ? $this :
            parent::addAttributeToFilter($attribute, $condition, $joinType);
    }

    /**
     * Get select count sql
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Zend_Db_Select_Exception
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        /* @var Select $select */
        $select = clone $this->getSelect();
        $havingClauses = $select->getPart(Select::HAVING);
        $select->reset(Select::ORDER);
        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);

        if (empty($havingClauses)) {
            $select->reset(Select::COLUMNS);
            $select->reset(Select::HAVING);
            $select->resetJoinLeft();
            $select->columns(new \Zend_Db_Expr('1'));
        }
        /* @var Select $countSelect */
        $countSelect = clone $select;
        $countSelect->reset();
        $countSelect->from($select, "COUNT(*)");

        return $countSelect;
    }
}
