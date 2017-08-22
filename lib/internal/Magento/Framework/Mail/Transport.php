<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

class Transport implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * @var \Zend\Mail\Transport\Sendmail
     */
    private $zendTransport;

    /**
     * @var \Magento\Framework\Mail\MessageInterface
     */
    private $message;

    /**
     * @param MessageInterface $message
     * @param null $parameters
     * @throws \InvalidArgumentException
     */
    public function __construct(\Magento\Framework\Mail\MessageInterface $message, $parameters = null)
    {
        $this->zendTransport = new \Zend\Mail\Transport\Sendmail($parameters);
        $this->message = $message;
    }

    /**
     * Send a mail using this transport
     *
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendMessage()
    {
        try {
            $this->zendTransport->send(
                \Zend\Mail\Message::fromString($this->message->getRawMessage())
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\MailException(new \Magento\Framework\Phrase($e->getMessage()), $e);
        }
    }
}
