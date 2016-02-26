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
     * @var \Magento\Framework\MessageQueue\LogFactory
     */
    private $logFactory;

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
     * @param LogFactory $logFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(LogFactory $logFactory, \Magento\Framework\Stdlib\DateTime\DateTime $dateTime)
    {
        $this->logFactory = $logFactory;
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
        $log = $this->logFactory->create();
        $code = $consumerName . '-' . $envelope->getMessageId();
        $code = md5($code);
        if (isset($this->registry[$code])) {
            throw new MessageLockException(new Phrase('Message code %1 already processed', [$code]));
        }

        $log->load($code, 'message_code');
        if ($log->getId()) {
            throw new MessageLockException(new Phrase('Message code %1 already processed', [$code]));
        }
        $log->setMessageCode($code);
        $log->setCreatedAt($this->dateTime->gmtTimestamp());
        $log->save();

        $this->registry[$code] = true;
        return $log;
    }
}
