<?php
/**
 * Mail Message
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

use Zend\Mime\Mime;
use Zend\Mime\Part;

/**
 * @todo get rid of temporal coupling (setMessageType() + setBody())
 * - deprecate setMessageType(), setBody() and getBody()
 * - implement setBodyHtml(), setBodyText(), getBodyHtml() and getBodyText()
 * - change usage in \Magento\Framework\Mail\Template\TransportBuilder::prepareMessage()
 */
class Message implements MessageInterface
{
    /**
     * @var \Zend\Mail\Message
     */
    private $zendMessage;

    /**
     * @param string $encoding
     */
    public function __construct($encoding = 'utf-8')
    {
        $this->zendMessage = new \Zend\Mail\Message;
        $this->zendMessage->setEncoding($encoding);
    }

    /**
     * Message type
     *
     * @var string
     */
    protected $messageType = self::TYPE_TEXT;

    /**
     * Set message type
     *
     * @param string $type
     * @return $this
     */
    public function setMessageType($type)
    {
        $this->messageType = $type;
        return $this;
    }

    /**
     * @param null|object|string|\Zend\Mime\Message $body
     * @return $this
     */
    public function setBody($body)
    {
        if (is_string($body) && $this->messageType === MessageInterface::TYPE_HTML) {
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
     * @return object
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
}
