<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report Products Review collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Review\Product;

class Collection extends \Magento\Catalog\Model\Resource\Product\Collection
{
    /**
     * Init Select
     *
     * @return \Magento\Catalog\Model\Resource\Product\Collection
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
                'last_created' => 'MAX(r.created_at)'
            ]
        )->group(
            'e.entity_id'
        );

        $joinCondition = [
            'e.entity_id = table_rating.entity_pk_value',
            $this->getConnection()->quoteInto('table_rating.store_id > ?', 0),
        ];

        $percentField = $this->getConnection()->quoteIdentifier('table_rating.percent');
        $sumPercentField = new \Zend_Db_Expr("SUM({$percentField})");
        $sumPercentApproved = new \Zend_Db_Expr('SUM(table_rating.percent_approved)');
        $countRatingId = new \Zend_Db_Expr('COUNT(table_rating.rating_id)');

        $this->getSelect()->joinLeft(
            ['table_rating' => $this->getTable('rating_option_vote_aggregated')],
            implode(' AND ', $joinCondition),
            [
                'avg_rating' => sprintf('%s/%s', $sumPercentField, $countRatingId),
                'avg_rating_approved' => sprintf('%s/%s', $sumPercentApproved, $countRatingId)
            ]
        );

        return $this;
    }

    /**
     * Add attribute to sort
     *
     * @param string $attribute
     * @param string $dir
     * @return $this|\Magento\Catalog\Model\Resource\Product\Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (in_array($attribute, ['review_cnt', 'last_created', 'avg_rating', 'avg_rating_approved'])) {
            $this->getSelect()->order($attribute . ' ' . $dir);
            return $this;
        }

        return parent::addAttributeToSort($attribute, $dir);
    }

    /**
     * Get select count sql
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        /* @var \Magento\Framework\DB\Select $select */
        $select = clone $this->getSelect();
        $select->reset(\Zend_Db_Select::ORDER);
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(\Zend_Db_Select::COLUMNS);
        $select->resetJoinLeft();
        $select->columns(new \Zend_Db_Expr('1'));

        /* @var \Magento\Framework\DB\Select $countSelect */
        $countSelect = clone $select;
        $countSelect->reset();
        $countSelect->from($select, "COUNT(*)");

        return $countSelect;
    }
}
