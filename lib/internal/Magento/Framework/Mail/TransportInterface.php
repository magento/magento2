<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
