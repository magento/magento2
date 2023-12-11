<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Mail;

use Magento\Framework\Mail\EmailMessageInterface;

/**
 * Class TransportInterfaceMock
 */
class TransportInterfaceMock implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * @var null|EmailMessageInterface
     */
    private $message;

    /**
     * TransportInterfaceMock constructor.
     *
     * @param null|EmailMessageInterface $message
     */
    public function __construct($message = null)
    {
        $this->message = $message;
    }

    /**
     * Mock of send a mail using transport
     *
     * @return void
     */
    public function sendMessage()
    {
        //phpcs:ignore Squiz.PHP.NonExecutableCode.ReturnNotRequired
        return;
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
