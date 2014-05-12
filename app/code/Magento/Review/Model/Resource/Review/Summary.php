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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array('primary_id' => new \Zend_Db_Expr('MAX(primary_id)'), 'store_id', 'entity_pk_value')
        )->group(
            array('entity_pk_value', 'store_id')
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
                array('rating_summary' => $ratingSummary),
                $adapter->quoteInto('primary_id = ?', $row['primary_id'])
            );
        }
        return $this;
    }
}
