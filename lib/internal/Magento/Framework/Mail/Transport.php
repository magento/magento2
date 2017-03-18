<?php
/**
 * Mail Transport
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;

class Transport implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * @var Sendmail
     */
    private $zendTransport;
    /**
     * @var \Magento\Framework\Mail\MessageInterface
     */
    protected $_message;

    /**
     * @param MessageInterface $message
     * @param null $parameters
     * @throws \InvalidArgumentException
     */
    public function __construct(\Magento\Framework\Mail\MessageInterface $message, $parameters = null)
    {
        $this->zendTransport = new Sendmail($parameters);
        $this->_message = $message;
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
                Message::fromString($this->_message->getRawMessage())
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\MailException(new \Magento\Framework\Phrase($e->getMessage()), $e);
        }
    }
}
