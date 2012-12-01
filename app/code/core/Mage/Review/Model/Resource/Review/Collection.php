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
 * @package     Mage_Review
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Review collection resource model
 *
 * @category    Mage
 * @package     Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Review_Model_Resource_Review_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Review table
     *
     * @var string
     */
    protected $_reviewTable;

    /**
     * Review detail table
     *
     * @var string
     */
    protected $_reviewDetailTable;

    /**
     * Review status table
     *
     * @var string
     */
    protected $_reviewStatusTable;

    /**
     * Review entity table
     *
     * @var string
     */
    protected $_reviewEntityTable;

    /**
     * Review store table
     *
     * @var string
     */
    protected $_reviewStoreTable;

    /**
     * Add store data flag
     * @var bool
     */
    protected $_addStoreDataFlag   = false;

    /**
     * Define module
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Review_Model_Review', 'Mage_Review_Model_Resource_Review');
        $this->_reviewTable         = $this->getTable('review');
        $this->_reviewDetailTable   = $this->getTable('review_detail');
        $this->_reviewStatusTable   = $this->getTable('review_status');
        $this->_reviewEntityTable   = $this->getTable('review_entity');
        $this->_reviewStoreTable    = $this->getTable('review_store');

    }

    /**
     * init select
     *
     * @return Mage_Review_Model_Resource_Review_Product_Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->join(array('detail' => $this->_reviewDetailTable),
                'main_table.review_id = detail.review_id',
                array('detail_id', 'title', 'detail', 'nickname', 'customer_id'));
        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $customerId
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addCustomerFilter($customerId)
    {
        $this->addFilter('customer',
            $this->getConnection()->quoteInto('detail.customer_id=?', $customerId),
            'string');
        return $this;
    }

    /**
     * Add store filter
     *
     * @param int|array $storeId
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addStoreFilter($storeId)
    {
        $inCond = $this->getConnection()->prepareSqlCondition('store.store_id', array('in' => $storeId));
        $this->getSelect()->join(array('store'=>$this->_reviewStoreTable),
            'main_table.review_id=store.review_id',
            array());
        $this->getSelect()->where($inCond);
        return $this;
    }

    /**
     * Add stores data
     *
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addStoreData()
    {
        $this->_addStoreDataFlag = true;
        return $this;
    }

    /**
     * Add entity filter
     *
     * @param int|string $entity
     * @param int $pkValue
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addEntityFilter($entity, $pkValue)
    {
        if (is_numeric($entity)) {
            $this->addFilter('entity',
                $this->getConnection()->quoteInto('main_table.entity_id=?', $entity),
                'string');
        } elseif (is_string($entity)) {
            $this->_select->join($this->_reviewEntityTable,
                'main_table.entity_id='.$this->_reviewEntityTable.'.entity_id',
                array('entity_code'));

            $this->addFilter('entity',
                $this->getConnection()->quoteInto($this->_reviewEntityTable.'.entity_code=?', $entity),
                'string');
        }

        $this->addFilter('entity_pk_value',
            $this->getConnection()->quoteInto('main_table.entity_pk_value=?', $pkValue),
            'string');

        return $this;
    }

    /**
     * Add status filter
     *
     * @param int|string $status
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addStatusFilter($status)
    {
        if (is_string($status)) {
            $statuses = array_flip(Mage::helper('Mage_Review_Helper_Data')->getReviewStatuses());
            $status = isset($statuses[$status]) ? $statuses[$status] : 0;
        }
        if (is_numeric($status)) {
            $this->addFilter('status',
                $this->getConnection()->quoteInto('main_table.status_id=?', $status),
                'string');
        }
        return $this;
    }

    /**
     * Set date order
     *
     * @param string $dir
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function setDateOrder($dir = 'DESC')
    {
        $this->setOrder('main_table.created_at', $dir);
        return $this;
    }

    /**
     * Add rate votes
     *
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addRateVotes()
    {
        foreach ($this->getItems() as $item) {
            $votesCollection = Mage::getModel('Mage_Rating_Model_Rating_Option_Vote')
                ->getResourceCollection()
                ->setReviewFilter($item->getId())
                ->setStoreFilter(Mage::app()->getStore()->getId())
                ->addRatingInfo(Mage::app()->getStore()->getId())
                ->load();
            $item->setRatingVotes($votesCollection);
        }

        return $this;
    }

    /**
     * Add reviews total count
     *
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addReviewsTotalCount()
    {
        $this->_select->joinLeft(
            array('r' => $this->_reviewTable),
            'main_table.entity_pk_value = r.entity_pk_value',
            array('total_reviews' => new Zend_Db_Expr('COUNT(r.review_id)'))
        )
        ->group('main_table.review_id');

        /*
         * Allow analytic functions usage
         */
        $this->_useAnalyticFunction = true;

        return $this;
    }

    /**
     * Load data
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        Mage::dispatchEvent('review_review_collection_load_before', array('collection' => $this));
        parent::load($printQuery, $logQuery);
        if ($this->_addStoreDataFlag) {
            $this->_addStoreData();
        }
        return $this;
    }

    /**
     * Add store data
     *
     */
    protected function _addStoreData()
    {
        $adapter = $this->getConnection();

        $reviewsIds = $this->getColumnValues('review_id');
        $storesToReviews = array();
        if (count($reviewsIds)>0) {
            $inCond = $adapter->prepareSqlCondition('review_id', array('in' => $reviewsIds));
            $select = $adapter->select()
                ->from($this->_reviewStoreTable)
                ->where($inCond);
            $result = $adapter->fetchAll($select);
            foreach ($result as $row) {
                if (!isset($storesToReviews[$row['review_id']])) {
                    $storesToReviews[$row['review_id']] = array();
                }
                $storesToReviews[$row['review_id']][] = $row['store_id'];
            }
        }

        foreach ($this as $item) {
            if (isset($storesToReviews[$item->getId()])) {
                $item->setStores($storesToReviews[$item->getId()]);
            } else {
                $item->setStores(array());
            }
        }
    }
}
