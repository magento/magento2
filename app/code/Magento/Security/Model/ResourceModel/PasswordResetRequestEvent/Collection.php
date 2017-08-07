<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\ResourceModel\PasswordResetRequestEvent;

/**
 * Password reset request event collection
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
     * @since 100.1.0
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
            \Magento\Security\Model\PasswordResetRequestEvent::class,
            \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent::class
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
