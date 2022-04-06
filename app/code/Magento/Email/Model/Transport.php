<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Psr\Log\LoggerInterface;

/**
 * Class that responsible for filling some message data before transporting it.
 * @see \Laminas\Mail\Transport\Sendmail is used for transport
 */
class Transport implements TransportInterface
{
    /**
     * Configuration path to source of Return-Path and whether it should be set at all
     * @see \Magento\Config\Model\Config\Source\Yesnocustom to possible values
     */
    const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';

    /**
     * Configuration path for custom Return-Path email
     */
    const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';

    /**
     * Whether return path should be set or no.
     *
     * Possible values are:
     * 0 - no
     * 1 - yes (set value as FROM address)
     * 2 - use custom value
     *
     * @var int
     */
    private $isSetReturnPath;

    /**
     * @var string|null
     */
    private $returnPathValue;

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
     * @param MessageInterface $message Email message object
     * @param ScopeConfigInterface $scopeConfig Core store config
     * @param null|string|array|\Traversable $parameters Config options for sendmail parameters
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        MessageInterface $message,
        ScopeConfigInterface $scopeConfig,
        $parameters = null,
        LoggerInterface $logger = null
    ) {
        $this->isSetReturnPath = (int) $scopeConfig->getValue(
            self::XML_PATH_SENDING_SET_RETURN_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $this->returnPathValue = $scopeConfig->getValue(
            self::XML_PATH_SENDING_RETURN_PATH_EMAIL,
            ScopeInterface::SCOPE_STORE
        );

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
            $laminasMessage = Message::fromString($this->message->getRawMessage())->setEncoding('utf-8');
            if (2 === $this->isSetReturnPath && $this->returnPathValue) {
                $laminasMessage->setSender($this->returnPathValue);
            } elseif (1 === $this->isSetReturnPath && $laminasMessage->getFrom()->count()) {
                $fromAddressList = $laminasMessage->getFrom();
                $fromAddressList->rewind();
                $laminasMessage->setSender($fromAddressList->current()->getEmail());
            }

            $this->laminasTransport->send($laminasMessage);
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw new MailException(new Phrase('Unable to send mail. Please try again later.'), $e);
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
