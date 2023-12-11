<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Phrase;
use Laminas\Mail\Message as LaminasMessage;
use Laminas\Mail\Transport\Sendmail;
use Psr\Log\LoggerInterface;

/**
 * Mail transport
 */
class Transport implements TransportInterface
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
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param MessageInterface $message
     * @param null|string|array|\Traversable $parameters
     * @param LoggerInterface|null $logger
     */
    public function __construct(MessageInterface $message, $parameters = null, LoggerInterface $logger = null)
    {
        $this->laminasTransport = new Sendmail($parameters);
        $this->message = $message;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
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
            $this->logger->error($e);
            throw new MailException(new Phrase('Unable to send mail. Please try again later.'));
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
