<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\Exception\MailException;
use Zend\Mail\Address as ZendAddress;
use Zend\Mail\AddressList;
use Zend\Mail\Message as ZendMessage;
use Zend\Mime\Message as ZendMimeMessage;

/**
 * Class EmailMessage
 */
class EmailMessage implements EmailMessageInterface
{
    /**
     * @var ZendMessage
     */
    private $message;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageFactory;

    /**
     * @var MailAddressListFactory
     */
    private $mailAddressListFactory;

    /**
     * @var MailAddressFactory
     */
    private $mailAddressFactory;

    /**
     * EmailMessage constructor
     *
     * @param MimeMessageInterface $body
     * @param array $to
     * @param MimeMessageInterfaceFactory $mimeMessageFactory
     * @param MailAddressFactory $mailAddressFactory
     * @param MailAddress[]|null $from
     * @param MailAddress[]|null $cc
     * @param MailAddress[]|null $bcc
     * @param MailAddress[]|null $replyTo
     * @param MailAddress|null $sender
     * @param string|null $subject
     * @param string|null $encoding
     *
     * @throws MailException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        MimeMessageInterface $body,
        array $to,
        MimeMessageInterfaceFactory $mimeMessageFactory,
        MailAddressFactory $mailAddressFactory,
        ?array $from = null,
        ?array $cc = null,
        ?array $bcc = null,
        ?array $replyTo = null,
        ?MailAddress $sender = null,
        ?string $subject = '',
        ?string $encoding = ''
    ) {
        $this->message = new ZendMessage();
        $mimeMessage = new ZendMimeMessage();
        $mimeMessage->setParts($body->getParts());
        $this->message->setBody($mimeMessage);
        if ($encoding) {
            $this->message->setEncoding($encoding);
        }
        if ($subject) {
            $this->message->setSubject($subject);
        }
        if ($sender) {
            $this->message->setSender($sender->getEmail(), $sender->getName());
        }
        if (count($to) < 1) {
            throw new MailException(__('Email message must have at list one addressee'));
        }
        $this->message->setTo(
            $this->convertMailAddressArrayToZendAddressList($to)
        );
        $this->message->setReplyTo(
            $this->convertMailAddressArrayToZendAddressList($replyTo)
        );
        $this->message->setFrom(
            $this->convertMailAddressArrayToZendAddressList($from)
        );
        $this->message->setCc(
            $this->convertMailAddressArrayToZendAddressList($cc)
        );
        $this->message->setBcc(
            $this->convertMailAddressArrayToZendAddressList($bcc)
        );
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->mailAddressFactory = $mailAddressFactory;
    }

    /**
     * @inheritDoc
     */
    public function getEncoding(): string
    {
        return $this->message->getEncoding();
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->message->getHeaders()->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getFrom(): ?array
    {
        return $this->convertAddressListToMailAddressList($this->message->getFrom());
    }

    /**
     * @inheritDoc
     */
    public function getTo(): array
    {
        return $this->convertAddressListToMailAddressList($this->message->getTo());
    }

    /**
     * @inheritDoc
     */
    public function getCc(): ?array
    {
        return $this->convertAddressListToMailAddressList($this->message->getCc());
    }

    /**
     * @inheritDoc
     */
    public function getBcc(): ?array
    {
        return $this->convertAddressListToMailAddressList($this->message->getBcc());
    }

    /**
     * @inheritDoc
     */
    public function getReplyTo(): ?array
    {
        return $this->convertAddressListToMailAddressList($this->message->getReplyTo());
    }

    /**
     * @inheritDoc
     */
    public function getSender(): ?MailAddress
    {
        /** @var ZendAddress $zendSender */
        if (!$zendSender = $this->message->getSender()) {
            return null;
        }

        return $this->mailAddressFactory->create(
            [
                'email' => $zendSender->getEmail(),
                'name' => $zendSender->getName()
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getSubject(): ?string
    {
        return $this->message->getSubject();
    }

    /**
     * @inheritDoc
     */
    public function getBody(): MimeMessageInterface
    {
        return $this->mimeMessageFactory->create(
            ['parts' => $this->message->getBody()->getParts()]
        );
    }

    /**
     * @inheritDoc
     */
    public function getBodyText(): string
    {
        return $this->message->getBodyText();
    }

    /**
     * @inheritdoc
     */
    public function getRawMessage(): string
    {
        return $this->toString();
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->message->toString();
    }

    /**
     * Converts AddressList to array
     *
     * @param AddressList $addressList
     * @return MailAddress[]
     */
    private function convertAddressListToMailAddressList(AddressList $addressList): array
    {
        $arrayList = [];
        foreach ($addressList as $address) {
            $arrayList[] =
                $this->mailAddressFactory->create(
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
     * @param MailAddress[] $arrayList
     * @return AddressList
     */
    private function convertMailAddressArrayToZendAddressList(array $arrayList): AddressList
    {
        $zendAddressList = new AddressList();
        foreach ($arrayList as $address) {
            $zendAddressList->add($address->getEmail(), $address->getName());
        }

        return $zendAddressList;
    }
}
