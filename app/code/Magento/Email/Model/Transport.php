<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Model;

use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Mail\Transport\TransportInterface as LaminasTransportInterface;
use Psr\Log\LoggerInterface;

/**
 * Class that responsible for filling some message data before transporting it.
 * @see \Laminas\Mail\Transport\Sendmail is used for transport
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Transport implements TransportInterface
{
    /**
     * Configuration path to source of Return-Path and whether it should be set at all
     * @see \Magento\Config\Model\Config\Source\Yesnocustom to possible values
     */
    public const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';

    /**
     * Configuration path for custom Return-Path email
     */
    public const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';

    /**
     * Configuration path for custom Transport
     */
    private const XML_PATH_TRANSPORT = 'system/smtp/transport';

    /**
     * Configuration path for SMTP Host
     */
    private const XML_PATH_HOST = 'system/smtp/host';

    /**
     * Configuration path for SMTP Port
     */
    private const XML_PATH_PORT = 'system/smtp/port';

    /**
     * Configuration path for SMTP Username
     */
    private const XML_PATH_USERNAME = 'system/smtp/username';

    /**
     * Configuration path for SMTP Password
     */
    private const XML_PATH_PASSWORD = 'system/smtp/password';

    /**
     * Configuration path for SMTP Auth type
     */
    private const XML_PATH_AUTH = 'system/smtp/auth';

    /**
     * Configuration path for SMTP SSL value
     */
    private const XML_PATH_SSL = 'system/smtp/ssl';

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LaminasTransportInterface|null
     */
    private $laminasTransport;

    /**
     * @var null|string|array|\Traversable
     */
    private $parameters;

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
        $this->message = $message;
        $this->scopeConfig = $scopeConfig;
        $this->parameters = $parameters;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Get the LaminasTransport based on the configuration.
     *
     * @return LaminasTransportInterface
     */
    public function getTransport(): LaminasTransportInterface
    {
        if ($this->laminasTransport === null) {
            $transport = $this->scopeConfig->getValue(
                self::XML_PATH_TRANSPORT,
                ScopeInterface::SCOPE_STORE
            );

            if ($transport === 'smtp') {
                $this->laminasTransport = $this->createSmtpTransport();
            } else {
                $this->laminasTransport = $this->createSendmailTransport();
            }
        }

        return $this->laminasTransport;
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

            $this->getTransport()->send($laminasMessage);
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

    /**
     * Create a Smtp LaminasTransport.
     *
     * @return Smtp
     */
    private function createSmtpTransport(): Smtp
    {
        $host = $this->scopeConfig->getValue(
            self::XML_PATH_HOST,
            ScopeInterface::SCOPE_STORE
        );

        $port = $this->scopeConfig->getValue(
            self::XML_PATH_PORT,
            ScopeInterface::SCOPE_STORE
        );

        $username = $this->scopeConfig->getValue(
            self::XML_PATH_USERNAME,
            ScopeInterface::SCOPE_STORE
        );

        $password = $this->scopeConfig->getValue(
            self::XML_PATH_PASSWORD,
            ScopeInterface::SCOPE_STORE
        );

        $auth = $this->scopeConfig->getValue(
            self::XML_PATH_AUTH,
            ScopeInterface::SCOPE_STORE
        );

        $ssl = $this->scopeConfig->getValue(
            self::XML_PATH_SSL,
            ScopeInterface::SCOPE_STORE
        );

        $options  = [
            'name' => 'localhost',
            'host' => $host,
            'port' => $port,
            'connection_config' => [
                'username' => $username,
                'password' => $password,
            ]
        ];

        if ($auth && $auth !== 'none') {
            $options['connection_class'] = $auth;
        }

        if ($ssl && $ssl !== 'none') {
            $options['connection_config']['ssl'] = $ssl;
        }

        $transport = new Smtp();
        $transport->setOptions(new SmtpOptions($options));

        return $transport;
    }

    /**
     * Create a Sendmail Laminas Transport
     *
     * @return Sendmail
     */
    private function createSendmailTransport(): Sendmail
    {
        return new Sendmail($this->parameters);
    }
}
