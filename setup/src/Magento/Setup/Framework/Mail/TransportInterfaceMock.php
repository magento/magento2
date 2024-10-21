<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Framework\Mail;

use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\TransportInterface;

/**
 * Mock for mail transport.
 */
class TransportInterfaceMock implements TransportInterface
{
    /**
     * @var EmailMessageInterface|null
     */
    private $message;

    /**
     * @param EmailMessageInterface|null $message
     */
    public function __construct($message = null)
    {
        $this->message = $message;
    }

    /**
     * @inheritDoc
     */
    public function sendMessage()
    {
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->message;
    }
}
