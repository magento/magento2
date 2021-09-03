<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\Exception\NotFoundException;
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
     * @throws NotFoundException
     */
    public function lock(EnvelopeInterface $envelope, $consumerName)
    {
        $lock = $this->lockFactory->create();
        $properties = $envelope->getProperties();
        if (empty($properties['message_id'])) {
            throw new NotFoundException(new Phrase("Property 'message_id' not found in properties."));
        }
        $code = $consumerName . '-' . $properties['message_id'];
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $code = md5($code);
        $this->reader->read($lock, $code);
        if ($lock->getId()) {
            throw new MessageLockException(new Phrase('The "%1" message code was already processed.', [$code]));
        }
        $this->writer->saveLock($lock);
        return $lock;
    }
}
