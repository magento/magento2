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
namespace Magento\Review\Model\Resource\Rating\Option\Vote;

/**
 * Rating votes collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Store list manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Review\Model\Resource\Rating\Option\CollectionFactory
     */
    protected $_ratingCollectionF;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\Resource\Rating\Option\CollectionFactory $ratingCollectionF
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Review\Model\Resource\Rating\Option\CollectionFactory $ratingCollectionF,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_ratingCollectionF = $ratingCollectionF;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Review\Model\Rating\Option\Vote', 'Magento\Review\Model\Resource\Rating\Option\Vote');
    }

    /**
     * Set review filter
     *
     * @param int $reviewId
     * @return $this
     */
    public function setReviewFilter($reviewId)
    {
        $this->getSelect()->where("main_table.review_id = ?", $reviewId);
        return $this;
    }

    /**
     * Set EntityPk filter
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityPkFilter($entityId)
    {
        $this->getSelect()->where("entity_pk_value = ?", $entityId);
        return $this;
    }

    /**
     * Set store filter
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreFilter($storeId)
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            return $this;
        }
        $this->getSelect()->join(
            array('rstore' => $this->getTable('review_store')),
            $this->getConnection()->quoteInto(
                'main_table.review_id=rstore.review_id AND rstore.store_id=?',
                (int)$storeId
            ),
            array()
        );
        return $this;
    }

    /**
     * Add rating info to select
     *
     * @param int $storeId
     * @return $this
     */
    public function addRatingInfo($storeId = null)
    {
        $adapter = $this->getConnection();
        $ratingCodeCond = $adapter->getIfNullSql('title.value', 'rating.rating_code');
        $this->getSelect()->join(
            array('rating' => $this->getTable('rating')),
            'rating.rating_id = main_table.rating_id',
            array('rating_code')
        )->joinLeft(
            array('title' => $this->getTable('rating_title')),
            $adapter->quoteInto(
                'main_table.rating_id=title.rating_id AND title.store_id = ?',
                (int)$this->_storeManager->getStore()->getId()
            ),
            array('rating_code' => $ratingCodeCond)
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            if ($storeId == null) {
                $storeId = $this->_storeManager->getStore()->getId();
            }

            if (is_array($storeId)) {
                $condition = $adapter->prepareSqlCondition('store.store_id', array('in' => $storeId));
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
     * @return $this
     */
    public function addOptionInfo()
    {
        $this->getSelect()->join(
            array('rating_option' => $this->getTable('rating_option')),
            'main_table.option_id = rating_option.option_id'
        );
        return $this;
    }

    /**
     * Add rating options
     *
     * @return $this
     */
    public function addRatingOptions()
    {
        if (!$this->getSize()) {
            return $this;
        }
        foreach ($this->getItems() as $item) {
            /** @var \Magento\Review\Model\Resource\Rating\Option\Collection $options */
            $options = $this->_ratingCollectionF->create();
            $options->addRatingFilter($item->getRatingId())->load();

            if ($item->getRatingId()) {
                $item->setRatingOptions($options);
            } else {
                return $this;
            }
        }
        return $this;
    }
}
