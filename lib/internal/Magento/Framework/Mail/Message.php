<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

use Zend\Mime\Mime;
use Zend\Mime\Part;

/**
 * Class Message for email transportation
 *
 * @deprecated 102.0.4
 * @see \Magento\Framework\Mail\EmailMessage
 */
class Message implements MailMessageInterface
{
    /**
     * @var \Zend\Mail\Message
     */
    protected $zendMessage;

    /**
     * Message type
     *
     * @var string
     */
    private $messageType = Mime::TYPE_TEXT;

    /**
     * Initialize dependencies.
     *
     * @param string $charset
     */
    public function __construct($charset = 'utf-8')
    {
        $this->zendMessage = new \Zend\Mail\Message();
        $this->zendMessage->setEncoding($charset);
    }

    /**
     * @inheritdoc
     *
     * @deprecated 101.0.8
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setMessageType($type)
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
    public function setBody($body)
    {
        if (is_string($body)) {
            $body = self::createMimeFromString($body, $this->messageType);
        }
        $this->zendMessage->setBody($body);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }

    /**
     * @inheritdoc
     */
    public function getBody()
    {
        return $this->zendMessage->getBody();
    }

    /**
     * @inheritdoc
     *
     * @deprecated 102.0.1 This function is missing the from name. The
     * setFromAddress() function sets both from address and from name.
     * @see setFromAddress()
     */
    public function setFrom($fromAddress)
    {
        $this->setFromAddress($fromAddress, null);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFromAddress($fromAddress, $fromName = null)
    {
        $this->zendMessage->setFrom($fromAddress, $fromName);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addCc($ccAddress)
    {
        $this->zendMessage->addCc($ccAddress);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($replyToAddress)
    {
        $this->zendMessage->setReplyTo($replyToAddress);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    /**
     * Create mime message from the string.
     *
     * @param string $body
     * @param string $messageType
     * @return \Zend\Mime\Message
     */
    private function createMimeFromString($body, $messageType)
    {
        $part = new Part($body);
        $part->setCharset($this->zendMessage->getEncoding());
        $part->setEncoding(Mime::ENCODING_QUOTEDPRINTABLE);
        $part->setType($messageType);
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart($part);
        return $mimeMessage;
    }

    /**
     * @inheritdoc
     */
    public function setBodyHtml($html)
    {
        $this->setMessageType(Mime::TYPE_HTML);
        return $this->setBody($html);
    }

    /**
     * @inheritdoc
     */
    public function setBodyText($text)
    {
        $this->setMessageType(Mime::TYPE_TEXT);
        return $this->setBody($text);
    }
}
