<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Phrase;
use Zend\Mail\Message as ZendMessage;
use Zend\Mail\Transport\Sendmail;

class Transport implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * @var Sendmail
     */
    private $zendTransport;

    /**
<<<<<<< HEAD
     * @var MessageInterface
=======
     * @var Message
>>>>>>> upstream/2.2-develop
     */
    private $message;

    /**
     * @param MessageInterface $message
     * @param null|string|array|\Traversable $parameters
     */
    public function __construct(MessageInterface $message, $parameters = null)
    {
        $this->zendTransport = new Sendmail($parameters);
        $this->message = $message;
    }

    /**
     * @inheritdoc
     */
    public function sendMessage()
    {
        try {
            $this->zendTransport->send(
                ZendMessage::fromString($this->message->getRawMessage())
            );
        } catch (\Exception $e) {
            throw new MailException(new Phrase($e->getMessage()), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }
}
