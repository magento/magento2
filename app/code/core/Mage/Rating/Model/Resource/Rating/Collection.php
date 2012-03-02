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
 * @category    Mage
 * @package     Mage_Rating
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Rating collection resource model
 *
 * @category    Mage
 * @package     Mage_Rating
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rating_Model_Resource_Rating_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * @var bool
     */
    protected $_isStoreJoined = false;

    /**
     * Resource initialization
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Rating_Model_Rating', 'Mage_Rating_Model_Resource_Rating');
    }

    /**
     * Add entity filter
     *
     * @param   int|string $entity
     * @return  Mage_Rating_Model_Resource_Rating_Collection
     */
    public function addEntityFilter($entity)
    {
        $adapter = $this->getConnection();

        $this->getSelect()
            ->join($this->getTable('rating_entity'),
                'main_table.entity_id=' . $this->getTable('rating_entity') . '.entity_id',
                array('entity_code'));

        if (is_numeric($entity)) {
            $this->addFilter('entity',
                $adapter->quoteInto($this->getTable('rating_entity') . '.entity_id=?', $entity),
                'string');
        } elseif (is_string($entity)) {
            $this->addFilter('entity',
                $adapter->quoteInto($this->getTable('rating_entity') . '.entity_code=?', $entity),
                'string');
        }
        return $this;
    }

    /**
     * set order by position field
     *
     * @param   string $dir
     * @return  Mage_Rating_Model_Resource_Rating_Collection
     */
    public function setPositionOrder($dir='ASC')
    {
        $this->setOrder('main_table.position', $dir);
        return $this;
    }

    /**
     * Set store filter
     *
     * @param int_type $storeId
     * @return Mage_Rating_Model_Resource_Rating_Collection
     */
    public function setStoreFilter($storeId)
    {
        $adapter = $this->getConnection();
        if (!is_array($storeId)) {
            $storeId = array($storeId === null ? -1 : $storeId);
        }
        if (empty($storeId)) {
            return $this;
        }
        if (!$this->_isStoreJoined) {
            $this->getSelect()
                ->distinct(true)
                ->join(
                    array('store'=>$this->getTable('rating_store')),
                    'main_table.rating_id = store.rating_id',
                    array())
        //        ->group('main_table.rating_id')
                ;
            $this->_isStoreJoined = true;
        }
        $inCond = $adapter->prepareSqlCondition('store.store_id', array(
            'in' => $storeId
        ));
        $this->getSelect()
            ->where($inCond);
        $this->setPositionOrder();
        return $this;
    }

    /**
     * Add options to ratings in collection
     *
     * @return Mage_Rating_Model_Resource_Rating_Collection
     */
    public function addOptionToItems()
    {
        $arrRatingId = $this->getColumnValues('rating_id');

        if (!empty($arrRatingId)) {
            $collection = Mage::getResourceModel('Mage_Rating_Model_Resource_Rating_Option_Collection')
                ->addRatingFilter($arrRatingId)
                ->setPositionOrder()
                ->load();

            foreach ($this as $rating) {
                $rating->setOptions($collection->getItemsByColumnValue('rating_id', $rating->getId()));
            }
        }

        return $this;
    }

    /**
     * Add entity summary to item
     *
     * @param int $entityPkValue
     * @param int $storeId
     * @return Mage_Rating_Model_Resource_Rating_Collection
     */
    public function addEntitySummaryToItem($entityPkValue, $storeId)
    {
        $arrRatingId = $this->getColumnValues('rating_id');
        if (count($arrRatingId) == 0) {
            return $this;
        }

        $adapter = $this->getConnection();

        $inCond = $adapter->prepareSqlCondition('rating_option_vote.rating_id', array(
            'in' => $arrRatingId
        ));
        $sumCond = new Zend_Db_Expr("SUM(rating_option_vote.{$adapter->quoteIdentifier('percent')})");
        $countCond = new Zend_Db_Expr('COUNT(*)');
        $select = $adapter->select()
            ->from(array('rating_option_vote'  => $this->getTable('rating_option_vote')),
                array(
                    'rating_id' => 'rating_option_vote.rating_id',
                    'sum'         => $sumCond,
                    'count'       => $countCond
                ))
            ->join(
                array('review_store' => $this->getTable('review_store')),
                'rating_option_vote.review_id=review_store.review_id AND review_store.store_id = :store_id',
                array())
            ->join(
                array('rst' => $this->getTable('rating_store')),
                'rst.rating_id = rating_option_vote.rating_id AND rst.store_id = :rst_store_id',
                array())
            ->join(array('review'              => $this->getTable('review')),
                'review_store.review_id=review.review_id AND review.status_id=1',
                array())
            ->where($inCond)
            ->where('rating_option_vote.entity_pk_value=:pk_value')
            ->group('rating_option_vote.rating_id');
        $bind = array(
            ':store_id' => (int)$storeId,
            ':rst_store_id' => (int)$storeId,
            ':pk_value'     => $entityPkValue
        );
        $data = $this->getConnection()->fetchAll($select, $bind);

        foreach ($data as $item) {
            $rating = $this->getItemById($item['rating_id']);
            if ($rating && $item['count']>0) {
                $rating->setSummary($item['sum']/$item['count']);
            }
        }
        return $this;
    }

    /**
     * Add rating store name
     *
     * @param int $storeId
     * @return Mage_Rating_Model_Resource_Rating_Collection
     */
    public function addRatingPerStoreName($storeId)
    {
        $adapter = $this->getConnection();
        $ratingCodeCond = $adapter->getIfNullSql('title.value', 'main_table.rating_code');
        $this->getSelect()
            ->joinLeft(array('title' => $this->getTable('rating_title')),
                $adapter->quoteInto('main_table.rating_id=title.rating_id AND title.store_id = ?', (int) $storeId),
                array('rating_code' => $ratingCodeCond));
        return $this;
    }

    /**
     * Add stores to collection
     *
     * @return Mage_Rating_Model_Resource_Rating_Collection
     */
    public function addStoresToCollection()
    {
        if (!$this->_isCollectionLoaded) {
            return $this;
        }
        $ratingIds = array();
        foreach ($this as $item) {
            $ratingIds[] = $item->getId();
            $item->setStores(array());
        }
        if (!$ratingIds) {
            return $this;
        }
        $adapter = $this->getConnection();

        $inCond = $adapter->prepareSqlCondition('rating_id', array(
            'in' => $ratingIds
        ));

        $this->_select = $adapter
            ->select()
            ->from($this->getTable('rating_store'))
            ->where($inCond);

        $data = $adapter->fetchAll($this->_select);
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $row) {
                $item = $this->getItemById($row['rating_id']);
                $item->setStores(array_merge($item->getStores(), array($row['store_id'])));
            }
        }
        return $this;
    }
}
