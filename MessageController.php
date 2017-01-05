<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\Phrase;

class MessageController
{
    /**
     * @var \Magento\Framework\MessageQueue\LockInterfaceFactory
     */
    private $lockFactory;

    /**
     * @var \Magento\Framework\MessageQueue\Lock\ReaderInterface
     */
    private $reader;

    /**
     * @var \Magento\Framework\MessageQueue\Lock\WriterInterface
     */
    private $writer;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\MessageQueue\LockInterfaceFactory $lockFactory
     * @param Lock\ReaderInterface $reader
     * @param Lock\WriterInterface $writer
     */
    public function __construct(
        \Magento\Framework\MessageQueue\LockInterfaceFactory $lockFactory,
        \Magento\Framework\MessageQueue\Lock\ReaderInterface $reader,
        \Magento\Framework\MessageQueue\Lock\WriterInterface $writer
    ) {
        $this->lockFactory = $lockFactory;
        $this->reader = $reader;
        $this->writer = $writer;
    }

    /**
     * Create lock corresponding to the provided message. Throw MessageLockException if lock is already created.
     *
     * @param EnvelopeInterface $envelope
     * @param string $consumerName
     * @return LockInterface
     * @throws MessageLockException
     */
    public function lock(EnvelopeInterface $envelope, $consumerName)
    {
        $lock = $this->lockFactory->create();
        $code = $consumerName . '-' . $envelope->getProperties()['message_id'];
        $code = md5($code);
        $this->reader->read($lock, $code);
        if ($lock->getId()) {
            throw new MessageLockException(new Phrase('Message code %1 already processed', [$code]));
        }
        $this->writer->saveLock($lock);
        return $lock;
    }
}
