<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\ResourceModel\PasswordResetRequestEvent;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Security\Model\PasswordResetRequestEvent as ModelPasswordResetRequestEvent;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent as ResourcePasswordResetRequestEvent;
use Psr\Log\LoggerInterface;

/**
 * Password reset request event collection
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
            ModelPasswordResetRequestEvent::class,
            ResourcePasswordResetRequestEvent::class
        );
    }

    /**
     * Filter by account reference
     *
     * @param string $reference
     * @return $this
     * @since 100.1.0
     */
    public function filterByAccountReference($reference)
    {
        $this->addFieldToFilter('account_reference', $reference);

        return $this;
    }

    /**
     * Filter by IP
     *
     * @param string $ip
     * @return $this
     * @since 100.1.0
     */
    public function filterByIp($ip)
    {
        $this->addFieldToFilter('ip', $ip);

        return $this;
    }

    /**
     * Filter by request type
     *
     * @param int $requestType
     * @return $this
     * @since 100.1.0
     */
    public function filterByRequestType($requestType)
    {
        $this->addFieldToFilter('request_type', $requestType);

        return $this;
    }

    /**
     * Filter by lifetime
     *
     * @param int $lifetime
     * @return $this
     * @since 100.1.0
     */
    public function filterByLifetime($lifetime)
    {
        $connection = $this->getConnection();
        $gmtTimestamp = $this->dateTime->gmtTimestamp();
        $this->addFieldToFilter(
            'created_at',
            ['gt' => $connection->formatDate($gmtTimestamp - $lifetime)]
        );

        return $this;
    }

    /**
     * Filter last item
     *
     * @return $this
     * @since 100.1.0
     */
    public function filterLastItem()
    {
        $this->addOrder('created_at', self::SORT_ORDER_DESC)->getSelect()->limit(1);

        return $this;
    }

    /**
     * Filter by IP or by account reference
     *
     * @param int $ip
     * @param string $accountReference
     * @return $this
     * @since 100.1.0
     */
    public function filterByIpOrAccountReference($ip, $accountReference)
    {
        $this->addFieldToFilter(
            ['ip', 'account_reference'],
            [
                ['eq' => $ip],
                ['eq' => $accountReference],
            ]
        );

        return $this;
    }

    /**
     * Delete records older than some value
     *
     * @param int $timestamp
     * @return $this
     * @since 100.1.0
     */
    public function deleteRecordsOlderThen($timestamp)
    {
        $this->getResource()->deleteRecordsOlderThen((int)$timestamp);

        return $this;
    }
}
