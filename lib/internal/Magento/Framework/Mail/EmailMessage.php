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
     * @param MailAddressList $to
     * @param MimeMessageInterfaceFactory $mimeMessageFactory
     * @param MailAddressListFactory $mailAddressListFactory
     * @param MailAddressFactory $mailAddressFactory
     * @param MailAddressList|null $from
     * @param MailAddressList|null $cc
     * @param MailAddressList|null $bcc
     * @param MailAddressList|null $replyTo
     * @param MailAddress|null $sender
     * @param string|null $subject
     * @param string|null $encoding
     *
     * @throws MailException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        MimeMessageInterface $body,
        MailAddressList $to,
        MimeMessageInterfaceFactory $mimeMessageFactory,
        MailAddressListFactory $mailAddressListFactory,
        MailAddressFactory $mailAddressFactory,
        ?MailAddressList $from = null,
        ?MailAddressList $cc = null,
        ?MailAddressList $bcc = null,
        ?MailAddressList $replyTo = null,
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
            $this->message->setSender($sender);
        }
        $this->message->setReplyTo($replyTo);
        if ($to->count() < 1) {
            throw new MailException(__('Email message must have at list one addressee'));
        }

        $this->message->setTo($to);
        $this->message->setFrom($from);
        $this->message->setCc($cc);
        $this->message->setBcc($bcc);
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->mailAddressListFactory = $mailAddressListFactory;
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
    public function getFrom(): ?MailAddressList
    {
        return $this->convertAddressListToMailAddressList($this->message->getFrom());
    }

    /**
     * @inheritDoc
     */
    public function getTo(): MailAddressList
    {
        return $this->convertAddressListToMailAddressList($this->message->getTo());
    }

    /**
     * @inheritDoc
     */
    public function getCc(): ?MailAddressList
    {
        return $this->convertAddressListToMailAddressList($this->message->getCc());
    }

    /**
     * @inheritDoc
     */
    public function getBcc(): ?MailAddressList
    {
        return $this->convertAddressListToMailAddressList($this->message->getBcc());
    }

    /**
     * @inheritDoc
     */
    public function getReplyTo(): ?MailAddressList
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
                'name' => $zendSender->getName(),
                'comment' => $zendSender->getComment()
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
     * @return MailAddressList
     * @throws MailException
     */
    private function convertAddressListToMailAddressList(AddressList $addressList): MailAddressList
    {
        /** @var MailAddressList $arrayList */
        $arrayList = $this->mailAddressListFactory->create();
        foreach ($addressList as $address) {
            $arrayList->add(
                $this->mailAddressFactory->create(
                    [
                        'email' => $address->getEmail(),
                        'name' => $address->getName(),
                        'comment' => $address->getComment()
                    ]
                )
            );
        }

        return $arrayList;
    }
}
