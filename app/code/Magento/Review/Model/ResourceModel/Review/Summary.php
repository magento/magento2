<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Model\ResourceModel\Review;

use Magento\Framework\Model\AbstractModel;

/**
 * Review summary resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Summary extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define module
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('review_entity_summary', 'entity_pk_value');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->where('store_id = ?', (int)$object->getStoreId());
        return $select;
    }

    /**
     * Re-aggregate all data by rating summary
     *
     * @param array $summary
     * @return $this
     */
    public function reAggregate($summary)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['primary_id' => new \Zend_Db_Expr('MAX(primary_id)'), 'store_id', 'entity_pk_value']
        )->group(
            ['entity_pk_value', 'store_id']
        );
        foreach ($connection->fetchAll($select) as $row) {
            if (isset($summary[$row['store_id']]) && isset($summary[$row['store_id']][$row['entity_pk_value']])) {
                $summaryItem = $summary[$row['store_id']][$row['entity_pk_value']];
                if ($summaryItem->getCount()) {
                    $ratingSummary = round($summaryItem->getSum() / $summaryItem->getCount());
                } else {
                    $ratingSummary = $summaryItem->getSum();
                }
            } else {
                $ratingSummary = 0;
            }
            $connection->update(
                $this->getMainTable(),
                ['rating_summary' => $ratingSummary],
                $connection->quoteInto('primary_id = ?', $row['primary_id'])
            );
        }
        return $this;
    }

    /**
     * Append review summary fields to product collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param int $storeId
     * @param string $entityCode
     * @return Summary
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function appendSummaryFieldsToCollection(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        int $storeId,
        string $entityCode
    ) {
        if (!$productCollection->isLoaded()) {
            $summaryEntitySubSelect = $this->getConnection()->select();
            $summaryEntitySubSelect
                ->from(
                    ['review_entity' => $this->getTable('review_entity')],
                    ['entity_id']
                )->where(
                    'entity_code = ?',
                    $entityCode
                );
            $joinCond = new \Zend_Db_Expr(
                "e.entity_id = review_summary.entity_pk_value AND review_summary.store_id = {$storeId}"
                . " AND review_summary.entity_type = ({$summaryEntitySubSelect})"
            );
            $productCollection->getSelect()
                ->joinLeft(
                    ['review_summary' => $this->getMainTable()],
                    $joinCond,
                    [
                        'reviews_count' => new \Zend_Db_Expr("IFNULL(review_summary.reviews_count, 0)"),
                        'rating_summary' => new \Zend_Db_Expr("IFNULL(review_summary.rating_summary, 0)")
                    ]
                );
        }

        return $this;
    }
}
