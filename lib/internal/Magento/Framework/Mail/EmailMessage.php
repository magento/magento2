<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Laminas\Mail\Exception\InvalidArgumentException as LaminasInvalidArgumentException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Laminas\Mail\Address as LaminasAddress;
use Laminas\Mail\AddressList;
use Laminas\Mime\Message as LaminasMimeMessage;
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
    private $mimeMessageFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param MimeMessageInterface $body
     * @param array $to
     * @param MimeMessageInterfaceFactory $mimeMessageFactory
     * @param AddressFactory $addressFactory
     * @param Address[]|null $from
     * @param Address[]|null $cc
     * @param Address[]|null $bcc
     * @param Address[]|null $replyTo
     * @param Address|null $sender
     * @param string|null $subject
     * @param string|null $encoding
     * @param LoggerInterface|null $logger
     * @throws InvalidArgumentException
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
        $mimeMessage = new LaminasMimeMessage();
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $mimeMessage->setParts($body->getParts());
        $this->zendMessage->setBody($mimeMessage);
        if ($subject) {
            $this->zendMessage->setSubject($subject);
        }
        if ($sender) {
            $this->zendMessage->setSender(
                $this->sanitiseEmail($sender->getEmail()),
                $sender->getName()
            );
        }
        if (count($to) < 1) {
            throw new InvalidArgumentException('Email message must have at least one addressee');
        }
        if ($to) {
            $this->zendMessage->setTo($this->convertAddressArrayToAddressList($to));
        }
        if ($replyTo) {
            $this->zendMessage->setReplyTo($this->convertAddressArrayToAddressList($replyTo));
        }
        if ($from) {
            $this->zendMessage->setFrom($this->convertAddressArrayToAddressList($from));
        }
        if ($cc) {
            $this->zendMessage->setCc($this->convertAddressArrayToAddressList($cc));
        }
        if ($bcc) {
            $this->zendMessage->setBcc($this->convertAddressArrayToAddressList($bcc));
        }
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @inheritDoc
     */
    public function getEncoding(): string
    {
        return $this->zendMessage->getEncoding();
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->zendMessage->getHeaders()->toArray();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getFrom(): ?array
    {
        return $this->convertAddressListToAddressArray($this->zendMessage->getFrom());
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getTo(): array
    {
        return $this->convertAddressListToAddressArray($this->zendMessage->getTo());
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getCc(): ?array
    {
        return $this->convertAddressListToAddressArray($this->zendMessage->getCc());
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getBcc(): ?array
    {
        return $this->convertAddressListToAddressArray($this->zendMessage->getBcc());
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    public function getReplyTo(): ?array
    {
        return $this->convertAddressListToAddressArray($this->zendMessage->getReplyTo());
    }

    /**
     * @inheritDoc
     */
    public function getSender(): ?Address
    {
        /** @var LaminasAddress $laminasSender */
        if (!$laminasSender = $this->zendMessage->getSender()) {
            return null;
        }
        return $this->addressFactory->create(
            [
                'email' => $laminasSender->getEmail(),
                'name' => $laminasSender->getName()
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getMessageBody(): MimeMessageInterface
    {
        return $this->mimeMessageFactory->create(
            ['parts' => $this->zendMessage->getBody()->getParts()]
        );
    }

    /**
     * @inheritDoc
     */
    public function getBodyText(): string
    {
        return $this->zendMessage->getBodyText();
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->zendMessage->toString();
    }

    /**
     * Converts AddressList to array
     *
     * @param AddressList $addressList
     * @return Address[]
     * @throws InvalidArgumentException
     */
    private function convertAddressListToAddressArray(AddressList $addressList): array
    {
        $arrayList = [];
        foreach ($addressList as $address) {
            $arrayList[] =
                $this->addressFactory->create(
                    [
                        'email' => $this->sanitiseEmail($address->getEmail()),
                        'name' => $address->getName()
                    ]
                );
        }

        return $arrayList;
    }

    /**
     * Converts MailAddress array to AddressList
     *
     * @param Address[] $arrayList
     * @return AddressList
     * @throws LaminasInvalidArgumentException|InvalidArgumentException
     */
    private function convertAddressArrayToAddressList(array $arrayList): AddressList
    {
        $laminasAddressList = new AddressList();
        foreach ($arrayList as $address) {
            try {
                $laminasAddressList->add(
                    $this->sanitiseEmail($address->getEmail()),
                    $address->getName()
                );
            } catch (LaminasInvalidArgumentException $e) {
                $this->logger->warning(
                    'Could not add an invalid email address to the mailing queue',
                    ['exception' => $e]
                );
                continue;
            }
        }

        return $laminasAddressList;
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
