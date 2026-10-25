<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Mail;

use Magento\Framework\Mail\MimeInterface;
use Symfony\Component\Mime\Message as SymfonyMessage;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Part\HtmlPart;
use Symfony\Component\Mime\Part\AbstractPart;

/**
 * Class Message for email transportation
 *
 * @deprecated 102.0.4 a new message implementation was added
 * @see \Magento\Framework\Mail\EmailMessage
 */
class Message implements MailMessageInterface
{
    /**
     * @var SymfonyMessage
     */
    protected SymfonyMessage $symfonyMessage;

    /**
     * @var string
     */
    private string $messageType = MimeInterface::TYPE_TEXT;

    /**
     * @var string
     */
    protected string $charset;

    /**
     * Initialize dependencies.ßß
     *
     * @param string $charset
     */
    public function __construct(string $charset = 'utf-8')
    {
        $this->charset = $charset;
    }

    /**
     * @inheritdoc
     *
     * @deprecated 101.0.8
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setMessageType($type): self
    {
        $this->messageType = $type;
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @deprecated 101.0.8
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setBody($body): self
    {
        if (is_string($body)) {
            $body = $this->createMimeFromString($body, $this->messageType);
        }

        $this->symfonyMessage = $body;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject): self
    {
        $this->symfonyMessage->getHeaders()->addTextHeader('Subject', $subject);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject(): ?string
    {
        return $this->symfonyMessage->getHeaders()->getHeaderBody('Subject');
    }

    /**
     * @inheritdoc
     */
    public function getBody(): AbstractPart
    {
        return $this->symfonyMessage->getBody();
    }

    /**
     * @inheritdoc
     *
     * @deprecated 102.0.1 This function is missing the from name. The
     * setFromAddress() function sets both from address and from name.
     * @see setFromAddress()
     */
    public function setFrom($fromAddress): self
    {
        $this->setFromAddress($fromAddress, null);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFromAddress($fromAddress, $fromName = null): self
    {
        $this->symfonyMessage->getHeaders()->addMailboxListHeader('From', [$fromAddress, $fromName]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addTo($toAddress): self
    {
        $this->symfonyMessage->getHeaders()->addMailboxListHeader('To', [$toAddress]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addCc($ccAddress): self
    {
        $this->symfonyMessage->getHeaders()->addMailboxListHeader('Cc', [$ccAddress]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addBcc($bccAddress): self
    {
        $this->symfonyMessage->getHeaders()->addMailboxListHeader('Bcc', [$bccAddress]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($replyToAddress): self
    {
        $this->symfonyMessage->getHeaders()->addMailboxListHeader('Reply-To', [$replyToAddress]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRawMessage(): string
    {
        return $this->symfonyMessage->toString();
    }

    /**
     * Create mime message from the string.
     *
     * @param string $body
     * @param string $messageType
     * @return SymfonyMessage
     */
    private function createMimeFromString(string $body, string $messageType): SymfonyMessage
    {
        if ($messageType == MimeInterface::TYPE_HTML) {
            $part = new TextPart($body, $this->charset, 'html', MimeInterface::ENCODING_QUOTED_PRINTABLE);
            $part->setDisposition('inline');
            return new SymfonyMessage(null, $part);
        }

        $part = new TextPart($body, $this->charset, 'plain', MimeInterface::ENCODING_QUOTED_PRINTABLE);
        $part->setDisposition('inline');
        return new SymfonyMessage(null, $part);
    }

    /**
     * @inheritdoc
     */
    public function setBodyHtml($html): self
    {
        $this->setMessageType(MimeInterface::TYPE_HTML);
        $this->setBody($html);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setBodyText($text): self
    {
        $this->setMessageType(MimeInterface::TYPE_TEXT);
        $this->setBody($text);
        return $this;
    }
}
