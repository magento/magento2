<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Phrase;
use Laminas\Mail\Message as LaminasMessage;
use Laminas\Mail\Transport\Sendmail;

/**
 * Mail transport
 */
class Transport implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * @var Sendmail
     */
    private $laminasTransport;

    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @param MessageInterface $message
     * @param null|string|array|\Traversable $parameters
     */
    public function __construct(MessageInterface $message, $parameters = null)
    {
        $this->laminasTransport = new Sendmail($parameters);
        $this->message = $message;
    }

    /**
     * @inheritdoc
     */
    public function sendMessage()
    {
        try {
            $this->laminasTransport->send(
                LaminasMessage::fromString($this->message->getRawMessage())
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
