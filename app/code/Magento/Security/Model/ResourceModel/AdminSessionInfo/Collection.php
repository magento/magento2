<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\ResourceModel\AdminSessionInfo;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Security\Model\AdminSessionInfo as ModelAdminSessionInfo;
use Magento\Security\Model\ResourceModel\AdminSessionInfo as ResourceAdminSessionInfo;
use Psr\Log\LoggerInterface;

/**
 * Admin Session Info collection
 *
 * @api
 * @since 100.1.0
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     * @since 100.1.0
     */
    protected $_idFieldName = 'id';

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param DateTime $dateTime
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
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
     * @since 100.1.0
     */
    protected function _construct()
    {
        $this->_init(
            ModelAdminSessionInfo::class,
            ResourceAdminSessionInfo::class
        );
    }

    /**
     * Update active sessions status except a specific one
     *
     * @param int $status
     * @param int $userId
     * @param string $sessionIdToExclude
     * @param int $updateOlderThen
     * @return int The number of affected rows.
     * @since 100.1.0
     */
    public function updateActiveSessionsStatus(
        $status,
        $userId,
        $sessionIdToExclude,
        $updateOlderThen = null
    ) {
        return $this->getResource()->updateStatusByUserId(
            $status,
            $userId,
            [ModelAdminSessionInfo::LOGGED_IN],
            [$sessionIdToExclude],
            $updateOlderThen
        );
    }

    /**
     * Filter by user
     *
     * @param int $userId
     * @param int $status
     * @param null|string $sessionIdToExclude
     * @return $this
     * @since 100.1.0
     */
    public function filterByUser(
        $userId,
        $status = ModelAdminSessionInfo::LOGGED_IN,
        $sessionIdToExclude = null
    ) {
        $this->addFieldToFilter('user_id', $userId);
        $this->addFieldToFilter('status', $status);
        if (null !== $sessionIdToExclude) {
            $this->addFieldToFilter('id', ['neq' => $sessionIdToExclude]);
        }
        return $this;
    }

    /**
     * Filter expired sessions
     *
     * @param int $sessionLifeTime
     * @return $this
     * @since 100.1.0
     */
    public function filterExpiredSessions($sessionLifeTime)
    {
        $connection = $this->getConnection();
        $gmtTimestamp = $this->dateTime->gmtTimestamp();
        $this->addFieldToFilter(
            'updated_at',
            ['gt' => $connection->formatDate($gmtTimestamp - $sessionLifeTime)]
        );
        return $this;
    }

    /**
     * Delete sessions older than some value
     *
     * @param int $timestamp
     * @return $this
     * @since 100.1.0
     */
    public function deleteSessionsOlderThen($timestamp)
    {
        $this->getResource()->deleteSessionsOlderThen((int) $timestamp);

        return $this;
    }
}
