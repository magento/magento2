<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Setup\Exception;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address as SymfonyAddress;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Message as SymfonyMessage;
use Psr\Log\LoggerInterface;

/**
 * Magento Framework Email message
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailMessage extends Message implements EmailMessageInterface
{
    /**
     * @var MimeMessageInterfaceFactory
     */
    private MimeMessageInterfaceFactory $mimeMessageFactory;

    /**
     * @var AddressFactory
     */
    private AddressFactory $addressFactory;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @var Mailer
     */
    protected Mailer $mailer;

    /**
     * Constructor
     *
     * @param MimeMessageInterface $body
     * @param array $to
     * @param MimeMessageInterfaceFactory $mimeMessageFactory
     * @param AddressFactory $addressFactory
     * @param array|null $from
     * @param array|null $cc
     * @param array|null $bcc
     * @param array|null $replyTo
     * @param Address|null $sender
     * @param string|null $subject
     * @param string|null $encoding
     * @param LoggerInterface|null $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __construct(
        MimeMessageInterface $body,
        array $to,
        MimeMessageInterfaceFactory $mimeMessageFactory,
        AddressFactory $addressFactory,
        ?array $from = null,
        ?array $cc = null,
        ?array $bcc = null,
        ?array $replyTo = null,
        ?Address $sender = null,
        ?string $subject = '',
        ?string $encoding = 'utf-8',
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($encoding);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->addressFactory = $addressFactory;
        $this->symfonyMessage = $body->getMimeMessage();
        $this->setBody($this->symfonyMessage);
        if (!empty($subject)) {
            $this->symfonyMessage->getHeaders()->addTextHeader('Subject', $subject);
        }

        $this->setSender($sender);
        $this->setRecipients($to, 'To');
        $this->setRecipients($replyTo, 'Reply-To');
        $this->setRecipients($from, 'From');
        $this->setRecipients($cc, 'Cc');
        $this->setRecipients($bcc, 'Bcc');
    }

    /**
     * Get Symfony Message
     *
     * @return SymfonyMessage
     */
    public function getSymfonyMessage(): SymfonyMessage
    {
        return $this->symfonyMessage;
    }

    /**
     * Set the sender of the email
     *
     * @param Address|null $sender
     */
    private function setSender(?Address $sender): void
    {
        if ($sender) {
            $this->symfonyMessage->getHeaders()->addMailboxHeader(
                'Sender',
                new SymfonyAddress($this->sanitiseEmail($sender->getEmail()), $sender->getName())
            );
        }
    }

    /**
     * Set recipients for the message
     *
     * @param array|null $addresses
     * @param string $method
     */
    private function setRecipients(?array $addresses, string $method): void
    {
        if ($method === 'to' && (empty($addresses) || count($addresses) < 1)) {
            throw new InvalidArgumentException('Email message must have at least one addressee');
        }

        if (!$addresses) {
            return;
        }

        $recipients = [];
        foreach ($addresses as $address) {
            try {
                if ($address instanceof Address) {
                    $recipients[] = new SymfonyAddress(
                        $this->sanitiseEmail($address->getEmail()),
                        $address->getName() ?? ''
                    );
                } else {
                    $recipients[] = new SymfonyAddress(
                        $this->sanitiseEmail($address['email']),
                        $address['name'] ?? ''
                    );
                }
            } catch (\Exception $e) {
                $this->logger->warning(
                    'Could not add an invalid email address to the mailing queue',
                    ['exception' => $e]
                );
                continue;
            }

        }

        $this->symfonyMessage->getHeaders()->addMailboxListHeader($method, $recipients);
    }

    /**
     * @inheritDoc
     */
    public function getEncoding(): string
    {
        return $this->symfonyMessage->getHeaders()->getHeaderBody('Content-Transfer-Encoding');
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->symfonyMessage->getHeaders()->toArray();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getFrom(): ?array
    {
        return $this->getAddresses('From');
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getTo(): array
    {
        return $this->getAddresses('To') ?? [];
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getCc(): ?array
    {
        return $this->getAddresses('Cc');
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getBcc(): ?array
    {
        return $this->getAddresses('Bcc');
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getReplyTo(): ?array
    {
        return $this->getAddresses('Reply-To');
    }

    /**
     * Get addresses from a header.
     *
     * @param string $headerName
     * @return array|null
     */
    private function getAddresses(string $headerName): ?array
    {
        $header = $this->symfonyMessage->getHeaders()->get($headerName);
        if ($header) {
            return $this->convertAddressListToAddressArray($header->getAddresses());
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSender(): ?Address
    {
        $senderHeader = $this->symfonyMessage->getHeaders()->get('Sender');
        if (!$senderHeader) {
            return null;
        }

        $senderAddress = $senderHeader->getAddress();
        if (!$senderAddress) {
            return null;
        }

        return $this->addressFactory->create([
            'email' => $senderAddress->getAddress(),
            'name' => $senderAddress->getName()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getMessageBody(): MimeMessageInterface
    {
        $parts = [];
        if ($this->symfonyMessage->getBody() instanceof TextPart) {
            $parts[] = $this->symfonyMessage->getBody();
        }

        return $this->mimeMessageFactory->create(['parts' => $parts]);
    }

    /**
     * @inheritDoc
     */
    public function getBodyText(): string
    {
        return $this->symfonyMessage->getTextBody() ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getBodyHtml(): string
    {
        return $this->symfonyMessage->getHtmlBody() ?? '';
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->symfonyMessage->toString();
    }

    /**
     * ConvertAddress List To Address Array
     *
     * @param array $addressList
     * @return array
     */
    private function convertAddressListToAddressArray(array $addressList): array
    {
        return array_map(function ($address) {
            return $this->addressFactory->create([
                'email' => $this->sanitiseEmail($address->getAddress()),
                'name' => $address->getName()
            ]);
        }, $addressList);
    }

    /**
     * Sanitise email address
     *
     * @param ?string $email
     * @return ?string
     * @throws InvalidArgumentException
     */
    private function sanitiseEmail(?string $email): ?string
    {
        if (!empty($email) && str_starts_with($email, '=?')) {
            $decodedValue = iconv_mime_decode($email, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
            if (str_contains($decodedValue, ' ')) {
                throw new InvalidArgumentException('Invalid email format');
            }
        }

        return $email;
    }
}
