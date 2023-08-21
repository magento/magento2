<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

/**
 * Mail Transport interface
 *
 * @api
 * @since 100.0.2
 */
interface TransportInterface
{
    /**
     * Send a mail using this transport
     *
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendMessage();

    /**
     * Get message
     *
     * @return \Magento\Framework\Mail\MessageInterface
     * @since 101.0.0
     */
    public function getMessage();
}
