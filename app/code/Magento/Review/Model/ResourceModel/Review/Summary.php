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
 * @since 2.0.0
 */
class Summary extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define module
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
}
