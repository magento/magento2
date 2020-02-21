<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\ResourceModel;

use DateInterval;
use DateTime;
use Exception;
use Magento\Framework\MessageQueue\Lock\ReaderInterface;
use Magento\Framework\MessageQueue\Lock\WriterInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime as MagentoDateTime;
use Magento\MessageQueue\Model\Lock as LockModel;
use Magento\MessageQueue\Model\LockFactory;

/**
 * Class Lock to handle database lock table db transactions.
 */
class Lock extends AbstractDb implements ReaderInterface, WriterInterface
{
    /**
     * @var string
     */
    private const QUEUE_LOCK_TABLE = 'queue_lock';

    /**
     * @var MagentoDateTime
     */
    private $dateTime;

    /**
     * @var LockFactory
     */
    private $lockFactory;

    /**
     * @var integer
     */
    private $interval;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param MagentoDateTime $dateTime
     * @param LockFactory $lockFactory
     * @param string|null $connectionName
     * @param integer $interval
     */
    public function __construct(
        Context $context,
        MagentoDateTime $dateTime,
        LockFactory $lockFactory,
        ?string $connectionName = null,
        int $interval = 86400
    ) {
        $this->lockFactory = $lockFactory;
        $this->interval = $interval;
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Init.
     *
     * @return void
     *
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void
    {
        $this->_init(self::QUEUE_LOCK_TABLE, 'id');
    }

    /**
     * Read lock
     *
     * @param LockInterface $lock
     * @param string $code
     * @return void
     */
    public function read(LockInterface $lock, string $code): void
    {
        /** @var $object LockModel */
        $object = $this->lockFactory->create();
        $this->load($object, $code, 'message_code');
        $lock->setId($object->getId());
        $lock->setMessageCode($object->getMessageCode() ?: $code);
        $lock->setCreatedAt($object->getCreatedAt());
    }

    /**
     * Save lock
     *
     * @param LockInterface $lock
     *
     * @return void
     * @throws Exception
     */
    public function saveLock(LockInterface $lock): void
    {
        /** @var $object LockModel */
        $object = $this->lockFactory->create();
        $object->setMessageCode($lock->getMessageCode());
        $object->setCreatedAt($this->dateTime->gmtTimestamp());
        $this->save($object);
        $lock->setId($object->getId());
    }

    /**
     * Remove outdated locks
     *
     * @return void
     * @throws Exception
     */
    public function releaseOutdatedLocks(): void
    {
        $date = (new DateTime())->setTimestamp($this->dateTime->gmtTimestamp());
        $date->add(new DateInterval('PT' . $this->interval . 'S'));
        $this->getConnection()->delete($this->getTable(self::QUEUE_LOCK_TABLE), ['created_at <= ?' => $date]);
    }
}
