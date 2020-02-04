<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\ResourceModel\AdminSessionInfo;

/**
 * Admin Session Info collection
 *
 * @api
 * @since 100.1.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     * @since 100.1.0
     */
    protected $_idFieldName = 'id';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 100.1.0
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->dateTime = $dateTime;
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
            \Magento\Security\Model\AdminSessionInfo::class,
            \Magento\Security\Model\ResourceModel\AdminSessionInfo::class
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
            [\Magento\Security\Model\AdminSessionInfo::LOGGED_IN],
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
        $status = \Magento\Security\Model\AdminSessionInfo::LOGGED_IN,
        $sessionIdToExclude = null
    ) {
        $this->addFieldToFilter('user_id', $userId);
        $this->addFieldToFilter('status', $status);
        if (null !== $sessionIdToExclude) {
            $this->addFieldToFilter('session_id', ['neq' => $sessionIdToExclude]);
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
