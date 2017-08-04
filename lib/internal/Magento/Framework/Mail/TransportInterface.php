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
     * @return string
     * @since 2.2.0
     */
    public function getMessage();
}
