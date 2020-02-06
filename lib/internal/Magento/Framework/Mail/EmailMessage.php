<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Zend\Mail\Address as ZendAddress;
use Zend\Mail\AddressList;
use Zend\Mime\Message as ZendMimeMessage;

/**
 * Email message
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
     * EmailMessage constructor
     *
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
     * @throws InvalidArgumentException
     *
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
        ?string $encoding = 'utf-8'
    ) {
        parent::__construct($encoding);
        $mimeMessage = new ZendMimeMessage();
        $mimeMessage->setParts($body->getParts());
        $this->zendMessage->setBody($mimeMessage);
        if ($subject) {
            $this->zendMessage->setSubject($subject);
        }
        if ($sender) {
            $this->zendMessage->setSender($sender->getEmail(), $sender->getName());
        }
        if (count($to) < 1) {
            throw new InvalidArgumentException('Email message must have at list one addressee');
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
     */
    public function getFrom(): ?array
    {
        return $this->convertAddressListToAddressArray($this->zendMessage->getFrom());
    }

    /**
     * @inheritDoc
     */
    public function getTo(): array
    {
        return $this->convertAddressListToAddressArray($this->zendMessage->getTo());
    }

    /**
     * @inheritDoc
     */
    public function getCc(): ?array
    {
        return $this->convertAddressListToAddressArray($this->zendMessage->getCc());
    }

    /**
     * @inheritDoc
     */
    public function getBcc(): ?array
    {
        return $this->convertAddressListToAddressArray($this->zendMessage->getBcc());
    }

    /**
     * @inheritDoc
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
        /** @var ZendAddress $zendSender */
        if (!$zendSender = $this->zendMessage->getSender()) {
            return null;
        }

        return $this->addressFactory->create(
            [
                'email' => $zendSender->getEmail(),
                'name' => $zendSender->getName()
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
     */
    private function convertAddressListToAddressArray(AddressList $addressList): array
    {
        $arrayList = [];
        foreach ($addressList as $address) {
            $arrayList[] =
                $this->addressFactory->create(
                    [
                        'email' => $address->getEmail(),
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
     */
    private function convertAddressArrayToAddressList(array $arrayList): AddressList
    {
        $zendAddressList = new AddressList();
        foreach ($arrayList as $address) {
            $zendAddressList->add($address->getEmail(), $address->getName());
        }

        return $zendAddressList;
    }
}
