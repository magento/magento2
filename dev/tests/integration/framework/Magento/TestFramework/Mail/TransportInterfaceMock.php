<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Mail;

/**
 * Class TransportInterfaceMock
 */
class TransportInterfaceMock implements \Magento\Framework\Mail\TransportInterface
{
    private $message;

    /**
     * TransportInterfaceMock constructor.
     *
     * @param string $message
     */
    public function __construct($message = '')
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
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }
}
