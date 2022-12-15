<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\ResourceModel;

use DateInterval;
use DateTime;
use Magento\Framework\MessageQueue\Lock\ReaderInterface;
use Magento\Framework\MessageQueue\Lock\WriterInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\MessageQueue\Model\LockFactory;

/**
 * Class Lock to handle database lock table db transactions.
 */
class Lock extends AbstractDb implements ReaderInterface, WriterInterface
{
    /**#@+
     * Constants
     */
    public const QUEUE_LOCK_TABLE = 'queue_lock';
    /**#@-*/

    /**#@-*/
    private $dateTime;

    /**
     * @var LockFactory
     */
    private $lockFactory;

    /**
     * @var int
     */
    private $interval;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param LockFactory $lockFactory
     * @param ?string $connectionName
     * @param int $interval
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        LockFactory $lockFactory,
        $connectionName = null,
        $interval = 86400
    ) {
        $this->lockFactory = $lockFactory;
        $this->interval = $interval;
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::QUEUE_LOCK_TABLE, 'id');
    }

    /**
     * @inheritdoc
     */
    public function read(LockInterface $lock, $code)
    {
        $object = $this->lockFactory->create();
        $object->load($code, 'message_code');
        $lock->setId($object->getId());
        $lock->setMessageCode($object->getMessageCode() ?: $code);
        $lock->setCreatedAt($object->getCreatedAt());
    }

    /**
     * @inheritdoc
     */
    public function saveLock(LockInterface $lock)
    {
        $object = $this->lockFactory->create();
        $object->setMessageCode($lock->getMessageCode());
        $object->setCreatedAt($this->dateTime->gmtTimestamp());
        $object->save();
        $lock->setId($object->getId());
        $lock->setCreatedAt($object->getCreatedAt());
    }

    /**
     * @inheritdoc
     */
    public function releaseOutdatedLocks()
    {
        $date = (new DateTime())->setTimestamp($this->dateTime->gmtTimestamp());
        $date->add(new DateInterval('PT' . $this->interval . 'S'));
        $this->getConnection()->delete($this->getTable(self::QUEUE_LOCK_TABLE), ['created_at <= ?' => $date]);
    }
}
