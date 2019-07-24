<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Zend\Mail\AddressList;
use Zend\Mail\Message as ZendMessage;

/**
 * Class MessageEnvelope
 */
class MessageEnvelope implements MessageEnvelopeInterface
{
    /**
     * @var ZendMessage
     */
    private $message;

    /**
     * @var MimeMessageFactory
     */
    private $mimeMessageFactory;

    /**
     * MessageEnvelope constructor
     *
     * @param MimeMessageInterface $body
     * @param MimeMessageFactory $mimeMessageFactory
     * @param array|null $to
     * @param array|null $from
     * @param array|null $cc
     * @param array|null $bcc
     * @param array|null $replyTo
     * @param string|null $sender
     * @param string|null $senderName
     * @param string|null $subject
     * @param string|null $encoding
     */
    public function __construct(
        MimeMessageInterface $body,
        MimeMessageFactory $mimeMessageFactory,
        ?array $to = [],
        ?array $from = [],
        ?array $cc = [],
        ?array $bcc = [],
        ?array $replyTo = [],
        ?string $sender = '',
        ?string $senderName = '',
        ?string $subject = '',
        ?string $encoding = ''
    ) {
        $this->message = new ZendMessage();
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->setParts($body->getParts());
        $this->message->setBody($mimeMessage);
        if ($encoding) {
            $this->message->setEncoding($encoding);
        }
        if ($subject) {
            $this->message->setSubject($subject);
        }
        if ($sender) {
            $this->message->setSender($sender, $senderName);
        }
        $this->message->setReplyTo($replyTo);
        $this->message->setTo($to);
        $this->message->setFrom($from);
        $this->message->setCc($cc);
        $this->message->setBcc($bcc);
        $this->mimeMessageFactory = $mimeMessageFactory;
    }

    /**
     * Get the message encoding
     *
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->message->getEncoding();
    }

    /**
     * Access headers collection
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->message->getHeaders()->toArray();
    }

    /**
     * Retrieve list of From senders
     *
     * @return array
     */
    public function getFrom(): array
    {
        return $this->convertAddressListToArray($this->message->getFrom());
    }

    /**
     * Access the address list of the To header
     *
     * @return array
     */
    public function getTo(): array
    {
        return $this->convertAddressListToArray($this->message->getTo());
    }

    /**
     * Retrieve list of CC recipients
     *
     * @return array
     */
    public function getCc(): array
    {
        return $this->convertAddressListToArray($this->message->getCc());
    }

    /**
     * Retrieve list of BCC recipients
     *
     * @return array
     */
    public function getBcc(): array
    {
        return $this->convertAddressListToArray($this->message->getBcc());
    }

    /**
     * Access the address list of the Reply-To header
     *
     * @return array
     */
    public function getReplyTo(): array
    {
        return $this->convertAddressListToArray($this->message->getReplyTo());
    }

    /**
     * Retrieve the sender address, if any
     *
     * @return null|string
     */
    public function getSender(): ?string
    {
        return $this->message->getSender();
    }

    /**
     * Get the message subject header value
     *
     * @return null|string
     */
    public function getSubject(): ?string
    {
        return $this->message->getSubject();
    }

    /**
     * Return the currently set message body
     *
     * @return object|string|MimeMessageInterface
     */
    public function getBody(): MimeMessageInterface
    {
        return $this->mimeMessageFactory->create([
            'parts' => $this->message->getBody()->getParts()
        ]);
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
     * Serialize to string
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->message->toString();
    }

    /**
     * Converts AddressList to array
     *
     * @param AddressList $addressList
     * @return array
     */
    private function convertAddressListToArray(AddressList $addressList): array
    {
        $arrayList = [];
        foreach ($addressList as $email => $address) {
            if ($address->getName()) {
                $arrayList[$email] = $address->getName();
            } else {
                $arrayList[] = $address->getEmail();
            }
        }

        return $arrayList;
    }
}
