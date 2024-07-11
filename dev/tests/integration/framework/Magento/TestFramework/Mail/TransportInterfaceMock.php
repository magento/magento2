<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Mail;

use Magento\Framework\Mail\EmailMessageInterface;

/**
 * Mock of mail transport interface
 */
class TransportInterfaceMock implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * @var null|EmailMessageInterface
     */
    private $message;

    /**
     * @var null|callable
     */
    private $onMessageSentCallback;

    /**
     * TransportInterfaceMock constructor.
     *
     * @param null|EmailMessageInterface $message
     * @param null|callable $onMessageSentCallback
     */
    public function __construct(
        $message = null,
        ?callable $onMessageSentCallback = null
    ) {
        $this->message = $message;
        $this->onMessageSentCallback = $onMessageSentCallback;
    }

    /**
     * Mock of send a mail using transport
     *
     * @return void
     */
    public function sendMessage()
    {
        $this->onMessageSentCallback && call_user_func($this->onMessageSentCallback, $this->message);
    }

    /**
     * Get message
     *
     * @return null|EmailMessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }
}
