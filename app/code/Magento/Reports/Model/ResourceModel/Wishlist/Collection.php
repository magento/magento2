<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Wishlist;

/**
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Wishlist table name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_wishlistTable;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     * @since 2.0.0
     */
    protected $_customerResFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResFactory
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_customerResFactory = $customerResFactory;
    }

    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Wishlist\Model\Wishlist::class, \Magento\Wishlist\Model\ResourceModel\Wishlist::class);
        $this->setWishlistTable($this->getTable('wishlist'));
    }

    /**
     * Set wishlist table name
     * @codeCoverageIgnore
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setWishlistTable($value)
    {
        $this->_wishlistTable = $value;
        return $this;
    }

    /**
     * Retrieve wishlist table name
     * @codeCoverageIgnore
     *
     * @return string
     * @since 2.0.0
     */
    public function getWishlistTable()
    {
        return $this->_wishlistTable;
    }

    /**
     * Retrieve wishlist customer count
     *
     * @return array
     * @since 2.0.0
     */
    public function getWishlistCustomerCount()
    {
        /** @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $collection = $this->_customerResFactory->create();

        $customersSelect = $collection->getSelectCountSql();

        $countSelect = clone $customersSelect;
        $countSelect->joinLeft(
            ['wt' => $this->getWishlistTable()],
            'wt.customer_id = e.entity_id',
            []
        )->group(
            'wt.wishlist_id'
        );
        $count = $collection->count();
        $resultSelect = $this->getConnection()->select()->union(
            [$customersSelect, $count],
            \Magento\Framework\DB\Select::SQL_UNION_ALL
        );
        list($customers, $count) = $this->getConnection()->fetchCol($resultSelect);

        return [$count * 100 / $customers, $count];
    }

    /**
     * Get shared items collection count
     *
     * @return int
     * @since 2.0.0
     */
    public function getSharedCount()
    {
        /** @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $collection = $this->_customerResFactory->create();
        $countSelect = $collection->getSelectCountSql();
        $countSelect->joinLeft(
            ['wt' => $this->getWishlistTable()],
            'wt.customer_id = e.entity_id',
            []
        )->where(
            'wt.shared > 0'
        )->group(
            'wt.wishlist_id'
        );
        return $countSelect->getConnection()->fetchOne($countSelect);
    }
}
