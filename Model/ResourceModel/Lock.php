<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\ResourceModel;

use \Magento\Framework\MessageQueue\Lock\ReaderInterface;
use \Magento\Framework\MessageQueue\Lock\WriterInterface;

/**
 * Class Lock to handle database lock table db transactions.
 * @since 2.1.0
 */
class Lock extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements ReaderInterface, WriterInterface
{
    /**#@+
     * Constants
     */
    const QUEUE_LOCK_TABLE = 'queue_lock';
    /**#@-*/

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\MessageQueue\Model\LockFactory
     * @since 2.1.0
     */
    private $lockFactory;

    /**
     * @var integer
     * @since 2.1.0
     */
    private $interval;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\MessageQueue\Model\LockFactory $lockFactory
     * @param null $connectionName
     * @param integer $interval
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\MessageQueue\Model\LockFactory $lockFactory,
        $connectionName = null,
        $interval = 86400
    ) {
        $this->lockFactory = $lockFactory;
        $this->interval = $interval;
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * {@inheritDoc}
     * @since 2.1.0
     */
    protected function _construct()
    {
        $this->_init(self::QUEUE_LOCK_TABLE, 'id');
    }

    /**
     * {@inheritDoc}
     * @since 2.1.0
     */
    public function read(\Magento\Framework\MessageQueue\LockInterface $lock, $code)
    {
        $object = $this->lockFactory->create();
        $object->load($code, 'message_code');
        $lock->setId($object->getId());
        $lock->setMessageCode($object->getMessageCode() ?: $code);
        $lock->setCreatedAt($object->getCreatedAt());
    }

    /**
     * {@inheritDoc}
     * @since 2.1.0
     */
    public function saveLock(\Magento\Framework\MessageQueue\LockInterface $lock)
    {
        $object = $this->lockFactory->create();
        $object->setMessageCode($lock->getMessageCode());
        $object->setCreatedAt($this->dateTime->gmtTimestamp());
        $object->save();
    }

    /**
     * {@inheritDoc}
     * @since 2.1.0
     */
    public function releaseOutdatedLocks()
    {
        $date = (new \DateTime())->setTimestamp($this->dateTime->gmtTimestamp());
        $date->add(new \DateInterval('PT' . $this->interval . 'S'));
        $this->getConnection()->delete($this->getTable(self::QUEUE_LOCK_TABLE), ['created_at <= ?' => $date]);
    }
}
