<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\ResourceModel\Layout\Link;

use DateInterval;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime;
use Magento\Widget\Model\Layout\Link;
use Magento\Widget\Model\ResourceModel\Layout\Link as ResourceLink;
use Psr\Log\LoggerInterface;

/**
 * Layout update collection model
 */
class Collection extends AbstractCollection
{
    /**
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param DateTime $dateTime
     * @param mixed $connection
     * @param AbstractDb $resource
     * @param EntityFactory $entityFactory
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        protected readonly DateTime $dateTime,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Link::class, ResourceLink::class);
    }

    /**
     * Add filter by theme id
     *
     * @param int $themeId
     * @return $this
     */
    public function addThemeFilter($themeId)
    {
        $this->addFieldToFilter('theme_id', $themeId);
        return $this;
    }

    /**
     * Join with layout update table
     *
     * @param array $fields
     * @return $this
     */
    protected function _joinWithUpdate($fields = [])
    {
        $flagName = 'joined_with_update_table';
        if (!$this->getFlag($flagName)) {
            $this->getSelect()->join(
                ['update' => $this->getTable('layout_update')],
                'update.layout_update_id = main_table.layout_update_id',
                [$fields]
            );
            $this->setFlag($flagName, true);
        }

        return $this;
    }

    /**
     * Filter by temporary flag
     *
     * @param bool $isTemporary
     * @return $this
     */
    public function addTemporaryFilter($isTemporary)
    {
        $this->addFieldToFilter('main_table.is_temporary', $isTemporary ? 1 : 0);
        return $this;
    }

    /**
     * Get links for layouts that are older than specified number of days
     *
     * @param string $days
     * @return $this
     */
    public function addUpdatedDaysBeforeFilter($days)
    {
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $storeInterval = new DateInterval('P' . $days . 'D');
        $datetime->sub($storeInterval);
        $formattedDate = $this->dateTime->formatDate($datetime->getTimestamp());

        $this->_joinWithUpdate();
        $this->addFieldToFilter(
            'update.updated_at',
            ['notnull' => true]
        )->addFieldToFilter(
            'update.updated_at',
            ['lt' => $formattedDate]
        );

        return $this;
    }
}
