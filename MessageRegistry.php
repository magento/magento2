<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

class MessageRegistry
{
    /**
     * @var \Magento\Framework\MessageQueue\LogFactory
     */
    private $logFactory;

    /**
     * Initialize dependencies.
     *
     * @param LogFactory $logFactory
     */
    public function __construct(LogFactory $logFactory)
    {
        $this->logFactory = $logFactory;
    }

    /**
     * Register message
     *
     * @param EnvelopeInterface $envelope
     * @param string $consumerName
     * @return bool
     * @throws \Exception
     */
    public function register(EnvelopeInterface $envelope, $consumerName)
    {
        $log = $this->logFactory->create();
        $code = $consumerName . '-' . $envelope->getMessageId();
        $code = md5($code);
        $log->setMessageCode($code);
        try {
            $log->save();
        } catch (\Exception $exception) {
            //Exception code 23000 means Duplicate entry
            if ($exception->getCode() != 23000) {
                throw $exception;
            } else {
                return false;
            }
        }
        return true;
    }
}
