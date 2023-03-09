<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Design;

use DateTimeInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime as FrameworkDateTime;
use Magento\Theme\Model\Design as ModelDesign;
use Magento\Theme\Model\ResourceModel\Design as ResourceDesign;
use Magento\Theme\Model\ResourceModel\Design\Collection as DesignCollection;
use Psr\Log\LoggerInterface;

/**
 * Core Design resource collection
 */
class Collection extends AbstractCollection
{
    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param FrameworkDateTime $dateTime
     * @param mixed $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        protected readonly FrameworkDateTime $dateTime,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Core Design resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ModelDesign::class, ResourceDesign::class);
    }

    /**
     * Join store data to collection
     *
     * @return DesignCollection
     */
    public function joinStore()
    {
        return $this->join(['cs' => 'store'], 'cs.store_id = main_table.store_id', ['cs.name']);
    }

    /**
     * Add date filter to collection
     *
     * @param null|int|string|DateTimeInterface $date
     * @return $this
     */
    public function addDateFilter($date = null)
    {
        if ($date === null) {
            $date = $this->dateTime->formatDate(true);
        } else {
            $date = $this->dateTime->formatDate($date);
        }

        $this->addFieldToFilter('date_from', ['lteq' => $date]);
        $this->addFieldToFilter('date_to', ['gteq' => $date]);
        return $this;
    }

    /**
     * Add store filter to collection
     *
     * @param int|array $storeId
     * @return DesignCollection
     */
    public function addStoreFilter($storeId)
    {
        return $this->addFieldToFilter('store_id', ['in' => $storeId]);
    }
}
