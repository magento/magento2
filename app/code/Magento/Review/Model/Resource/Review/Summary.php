<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Resource\Review;

use Magento\Framework\Model\AbstractModel;

/**
 * Review summary resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Summary extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
     * @return \Zend_Db_Select
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
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()->from(
            $this->getMainTable(),
            ['primary_id' => new \Zend_Db_Expr('MAX(primary_id)'), 'store_id', 'entity_pk_value']
        )->group(
            ['entity_pk_value', 'store_id']
        );
        foreach ($adapter->fetchAll($select) as $row) {
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
            $adapter->update(
                $this->getMainTable(),
                ['rating_summary' => $ratingSummary],
                $adapter->quoteInto('primary_id = ?', $row['primary_id'])
            );
        }
        return $this;
    }
}
