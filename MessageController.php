<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\Phrase;

class MessageController
{
    /**
     * @var \Magento\Framework\MessageQueue\LockFactory
     */
    private $lockFactory;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * Initialize dependencies.
     *
     * @param LockFactory $lockFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(LockFactory $lockFactory, \Magento\Framework\Stdlib\DateTime\DateTime $dateTime)
    {
        $this->lockFactory = $lockFactory;
        $this->dateTime = $dateTime;
    }

     /**
     * Get message from registry
     *
     * @param EnvelopeInterface $envelope
     * @param string $consumerName
     * @return bool
     * @throws \Exception
     */
    public function lock(EnvelopeInterface $envelope, $consumerName)
    {
        $lock = $this->lockFactory->create();
        $code = $consumerName . '-' . $envelope->getMessageId();
        $code = md5($code);
        if (isset($this->registry[$code])) {
            throw new MessageLockException(new Phrase('Message code %1 already processed', [$code]));
        }

        $lock->load($code, 'message_code');
        if ($lock->getId()) {
            throw new MessageLockException(new Phrase('Message code %1 already processed', [$code]));
        }
        $lock->setMessageCode($code);
        $lock->setCreatedAt($this->dateTime->gmtTimestamp());
        $lock->save();

        $this->registry[$code] = true;
        return $lock;
    }
}
