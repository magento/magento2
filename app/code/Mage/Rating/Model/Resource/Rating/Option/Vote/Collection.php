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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Rating votes collection
 *
 * @category    Mage
 * @package     Mage_Rating
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rating_Model_Resource_Rating_Option_Vote_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Application instance
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Collection constructor
     *
     * @param Mage_Core_Model_Resource_Db_Abstract $resource
     * @param array $data
     * @throws InvalidArgumentException
     */
    public function __construct($resource = null, $data = array())
    {
        $this->_app = isset($data['app']) ? $data['app'] : Mage::app();

        if (!($this->_app instanceof Mage_Core_Model_App)) {
            throw new InvalidArgumentException('Required app object is invalid');
        }
        parent::__construct($resource);
    }

    /**
     * Define model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Rating_Model_Rating_Option_Vote', 'Mage_Rating_Model_Resource_Rating_Option_Vote');
    }

    /**
     * Set review filter
     *
     * @param int $reviewId
     * @return Mage_Rating_Model_Resource_Rating_Option_Vote_Collection
     */
    public function setReviewFilter($reviewId)
    {
        $this->getSelect()
            ->where("main_table.review_id = ?", $reviewId);
        return $this;
    }

    /**
     * Set EntityPk filter
     *
     * @param int $entityId
     * @return Mage_Rating_Model_Resource_Rating_Option_Vote_Collection
     */
    public function setEntityPkFilter($entityId)
    {
        $this->getSelect()
            ->where("entity_pk_value = ?", $entityId);
        return $this;
    }

    /**
     * Set store filter
     *
     * @param int $storeId
     * @return Mage_Rating_Model_Resource_Rating_Option_Vote_Collection
     */
    public function setStoreFilter($storeId)
    {
        if ($this->_app->isSingleStoreMode()) {
            return $this;
        }
        $this->getSelect()
            ->join(array('rstore'=>$this->getTable('review_store')),
                $this->getConnection()->quoteInto(
                    'main_table.review_id=rstore.review_id AND rstore.store_id=?',
                    (int)$storeId),
            array());
        return $this;
    }

    /**
     * Add rating info to select
     *
     * @param int $storeId
     * @return Mage_Rating_Model_Resource_Rating_Option_Vote_Collection
     */
    public function addRatingInfo($storeId=null)
    {
        $adapter=$this->getConnection();
        $ratingCodeCond = $adapter->getIfNullSql('title.value', 'rating.rating_code');
        $this->getSelect()
            ->join(
                array('rating'    => $this->getTable('rating')),
                'rating.rating_id = main_table.rating_id',
                array('rating_code'))
            ->joinLeft(
                array('title' => $this->getTable('rating_title')),
                $adapter->quoteInto('main_table.rating_id=title.rating_id AND title.store_id = ?',
                    (int)Mage::app()->getStore()->getId()),
                array('rating_code' => $ratingCodeCond));
        if (!$this->_app->isSingleStoreMode()) {
            if ($storeId == null) {
                $storeId = Mage::app()->getStore()->getId();
            }

            if (is_array($storeId)) {
                $condition = $adapter->prepareSqlCondition('store.store_id', array(
                    'in' => $storeId
                ));
            } else {
                $condition = $adapter->quoteInto('store.store_id = ?', $storeId);
            }

            $this->getSelect()->join(
                array('store' => $this->getTable('rating_store')),
                'main_table.rating_id = store.rating_id AND ' . $condition
            );
        }
        $adapter->fetchAll($this->getSelect());
        return $this;
    }

    /**
     * Add option info to select
     *
     * @return Mage_Rating_Model_Resource_Rating_Option_Vote_Collection
     */
    public function addOptionInfo()
    {
        $this->getSelect()
            ->join(array('rating_option' => $this->getTable('rating_option')),
                'main_table.option_id = rating_option.option_id');
        return $this;
    }

    /**
     * Add rating options
     *
     * @return Mage_Rating_Model_Resource_Rating_Option_Vote_Collection
     */
    public function addRatingOptions()
    {
        if (!$this->getSize()) {
            return $this;
        }
        foreach ($this->getItems() as $item) {
            $options = Mage::getModel('Mage_Rating_Model_Rating_Option')
                    ->getResourceCollection()
                    ->addRatingFilter($item->getRatingId())
                    ->load();

            if ($item->getRatingId()) {
                $item->setRatingOptions($options);
            } else {
                return;
            }
        }
        return $this;
    }
}
