<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Phrase;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * Mail transport
 */
class Transport implements TransportInterface
{
    /**
     * @var SendmailTransport
     */
    private SendmailTransport $symfonyTransport;

    /**
     * @var MessageInterface
     */
    private EmailMessageInterface $message;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @param EmailMessageInterface $message
     * @param LoggerInterface|null $logger
     */
    public function __construct(EmailMessageInterface $message, ?LoggerInterface $logger = null)
    {
        $this->symfonyTransport = new SendmailTransport();
        $this->message = $message;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function sendMessage(): void
    {
        try {
            $email = $this->message->getSymfonyMessage();
            $mailer = new Mailer($this->symfonyTransport);
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
     * @inheritdoc
     */
    public function getMessage(): EmailMessageInterface
    {
        return $this->message;
    }
}
