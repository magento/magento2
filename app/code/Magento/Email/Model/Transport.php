<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Email\Model;

use Magento\Framework\Mail\EmailMessageInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface as SymfonyTransportInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Auth\LoginAuthenticator;
use Symfony\Component\Mailer\Transport\Smtp\Auth\PlainAuthenticator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Phrase;
use Symfony\Component\Mime\Message as SymfonyMessage;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class that responsible for filling some message data before transporting it.
 * @see \Symfony\Component\Mailer\Transport is used for transport
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
    private int $isSetReturnPath;

    /**
     * @var string|null
     */
    private ?string $returnPathValue;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var SymfonyTransportInterface
     */
    private SymfonyTransportInterface $symfonyTransport;

    /**
     * @var EmailMessageInterface
     */
    private EmailMessageInterface $message;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @param EmailMessageInterface $message Email message object
     * @param ScopeConfigInterface $scopeConfig Core store config
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        EmailMessageInterface $message,
        ScopeConfigInterface $scopeConfig,
        ?LoggerInterface $logger = null
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
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Get the SymfonyTransport based on the configuration.
     *
     * @return SymfonyTransportInterface
     */
    public function getTransport(): SymfonyTransportInterface
    {
        if (!isset($this->symfonyTransport)) {
            $transportType = $this->scopeConfig->getValue(self::XML_PATH_TRANSPORT);
            if ($transportType === 'smtp') {
                $this->symfonyTransport = $this->createSmtpTransport();
            } else {
                $this->symfonyTransport = $this->createSendmailTransport();
            }
        }

        return $this->symfonyTransport;
    }

    /**
     * Build the DSN string for Symfony transport based on configuration.
     *
     * @return SymfonyTransportInterface
     */
    private function createSmtpTransport(): SymfonyTransportInterface
    {
        $host = $this->scopeConfig->getValue(self::XML_PATH_HOST, ScopeInterface::SCOPE_STORE);
        $port = (int) $this->scopeConfig->getValue(self::XML_PATH_PORT, ScopeInterface::SCOPE_STORE);
        $username = $this->scopeConfig->getValue(self::XML_PATH_USERNAME, ScopeInterface::SCOPE_STORE);
        $password = $this->scopeConfig->getValue(self::XML_PATH_PASSWORD, ScopeInterface::SCOPE_STORE);
        $auth = $this->scopeConfig->getValue(self::XML_PATH_AUTH, ScopeInterface::SCOPE_STORE);
        $ssl = $this->scopeConfig->getValue(self::XML_PATH_SSL, ScopeInterface::SCOPE_STORE);
        $tls = false;

        if ($ssl === 'tls') {
            $tls = true;
        }

        $transport = new EsmtpTransport($host, $port, $tls);
        if ($username) {
            $transport->setUsername($username);
        }
        if ($password) {
            $transport->setPassword($password);
        }

        switch ($auth) {
            case 'plain':
                $transport->setAuthenticators([new PlainAuthenticator()]);
                break;
            case 'login':
                $transport->setAuthenticators([new LoginAuthenticator()]);
                break;
            case 'none':
                break;
            default:
                throw new \InvalidArgumentException('Invalid authentication type: ' . $auth);
        }

        return $transport;
    }

    /**
     * Create a Sendmail transport for Symfony Mailer.
     *
     * @return SymfonyTransportInterface
     */
    private function createSendmailTransport(): SymfonyTransportInterface
    {
        $dsn = new Dsn('native', 'default');
        $nativeTransportFactory = new NativeTransportFactory();
        return $nativeTransportFactory->create($dsn);
    }

    /**
     * @inheritdoc
     */
    public function sendMessage(): void
    {
        try {
            $email = $this->message->getSymfonyMessage();
            $this->setReturnPath($email);
            $mailer = new Mailer($this->getTransport());
            $mailer->send($email);
        } catch (TransportExceptionInterface $transportException) {
            $this->logger->error('Transport error while sending email: ' . $transportException->getMessage());
            throw new MailException(
                new Phrase('Transport error: Unable to send mail at this time.'),
                $transportException
            );
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw new MailException(new Phrase('Unable to send mail. Please try again later.'), $e);
        }
    }

    /**
     * Set the return path if configured.
     *
     * @param SymfonyMessage $email
     */
    private function setReturnPath(SymfonyMessage $email): void
    {
        if ($this->isSetReturnPath === 2 && $this->returnPathValue) {
            $email->getHeaders()->addMailboxListHeader('Sender', [$this->returnPathValue]);
        } elseif ($this->isSetReturnPath === 1 &&
            !empty($fromAddresses = $email->getHeaders()->get('From')?->getAddresses())) {
            reset($fromAddresses);
            $email->getHeaders()->addMailboxListHeader('Sender', [current($fromAddresses)->getAddress()]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMessage(): EmailMessageInterface
    {
        return $this->message;
    }
}
