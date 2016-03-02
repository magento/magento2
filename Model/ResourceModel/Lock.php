<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\ResourceModel;

use \Magento\Framework\MessageQueue\Lock\ReaderInterface;
use \Magento\Framework\MessageQueue\Lock\WriterInterface;

/**
 * Class Lock to handle database lock table db transactions.
 */
class Lock extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements ReaderInterface, WriterInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\MessageQueue\Model\LockFactory
     */
    private $lockFactory;

    /**
     * @var int
     */
    private $interval;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param null $connectionName
     * @param int $interval
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
     */
    protected function _construct()
    {
        $this->_init('queue_lock', 'id');
    }

    /**
     * {@inheritDoc}
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
     */
    public function releaseOutdatedLocks($interval)
    {
        $date = (new \DateTime())->setTimestamp($this->dateTime->gmtTimestamp());
        $date->add(new \DateInterval('PT' . $interval . 'S'));
        $selectObject = $this->getConnection()->select();
        $selectObject
            ->from(['queue_lock' => $this->getTable('queue_lock')])
            ->where(
                'created_at <= ?',
                $date
            );
        $this->getConnection()->delete($selectObject);
    }
}
