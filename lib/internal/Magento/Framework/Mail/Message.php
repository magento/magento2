<?php
/**
 * Mail Message
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

use Magento\Framework\Exception\LocalizedException;
use Zend\Mime\Mime;
use Zend\Mime\Part;

/**
 * Class Message.
 */
class Message implements MailMessageInterface
{
    /**
     * @var \Zend\Mail\Message
     */
    private $zendMessage;

    /**
     * Message type
     *
     * @var string
     */
    protected $messageType = self::TYPE_TEXT;

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
     * {@inheritdoc}
     *
     * @deprecated
     * @see \Magento\Framework\Mail\Message::getBodyText
     * @see \Magento\Framework\Mail\Message::getBodyHtml
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setMessageType($type)
    {
        $this->messageType = $type;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setBody($body)
    {
        if (is_string($body) && $this->messageType === MailMessageInterface::TYPE_HTML) {
            $body = self::createHtmlMimeFromString($body);
        }
        $this->zendMessage->setBody($body);
        return $this;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated
     * @see \Magento\Framework\Mail\Message::getBodyText
     * @see \Magento\Framework\Mail\Message::getBodyHtml
     */
    public function getBody()
    {
        return $this->zendMessage->getBody();
    }

    /**
     * @param array|string $fromAddress
     * @return $this
     */
    public function setFrom($fromAddress)
    {
        $this->zendMessage->setFrom($fromAddress);
        return $this;
    }

    /**
     * @param array|string $toAddress
     * @return $this
     */
    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);
        return $this;
    }

    /**
     * @param array|string $ccAddress
     * @return $this
     */
    public function addCc($ccAddress)
    {
        $this->zendMessage->addCc($ccAddress);
        return $this;
    }

    /**
     * @param array|string $bccAddress
     * @return $this
     */
    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);
        return $this;
    }

    /**
     * @param array|string $replyToAddress
     * @return $this
     */
    public function setReplyTo($replyToAddress)
    {
        $this->zendMessage->setReplyTo($replyToAddress);
        return $this;
    }

    /**
     * @return string
     */
    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    /**
     * @param string $htmlBody
     * @return \Zend\Mime\Message
     */
    private function createHtmlMimeFromString($htmlBody)
    {
        $htmlPart = new Part($htmlBody);
        $htmlPart->setCharset($this->zendMessage->getEncoding());
        $htmlPart->setType(Mime::TYPE_HTML);
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart($htmlPart);
        return $mimeMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function setBodyHtml($html)
    {
        $this->setMessageType(self::TYPE_HTML);
        return $this->setBody($html);
    }

    /**
     * {@inheritdoc}
     */
    public function setBodyText($text)
    {
        $this->setMessageType(self::TYPE_TEXT);
        return $this->setBody($text);
    }

    /**
     * {@inheritdoc}
     */
    public function getBodyText()
    {
        if ($this->messageType != self::TYPE_TEXT) {
            throw new LocalizedException(
                __('Text message body is not set')
            );
        }
        return $this->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function getBodyHtml()
    {
        if ($this->messageType != self::TYPE_HTML) {
            throw new LocalizedException(
                __('HTML message body is not set')
            );
        }
        return $this->getBody();
    }
}
