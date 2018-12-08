<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

use Zend\Mime\Mime;
use Zend\Mime\Part;

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
    private $messageType = self::TYPE_TEXT;

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
<<<<<<< HEAD
            $body = $this->createHtmlMimeFromString($body);
=======
            $body = self::createHtmlMimeFromString($body);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        }
        $this->zendMessage->setBody($body);
        return $this;
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);
        return $this;
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function getBody()
    {
        return $this->zendMessage->getBody();
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function setFrom($fromAddress)
    {
        $this->zendMessage->setFrom($fromAddress);
        return $this;
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);
        return $this;
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function addCc($ccAddress)
    {
        $this->zendMessage->addCc($ccAddress);
        return $this;
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);
        return $this;
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function setReplyTo($replyToAddress)
    {
        $this->zendMessage->setReplyTo($replyToAddress);
        return $this;
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
     */
    public function setBodyHtml($html)
    {
        $this->setMessageType(self::TYPE_HTML);
        return $this->setBody($html);
    }

    /**
     * @inheritdoc
     */
    public function setBodyText($text)
    {
        $this->setMessageType(self::TYPE_TEXT);
        return $this->setBody($text);
    }

    /**
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * Create HTML mime message from the string.
     *
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
<<<<<<< HEAD
=======

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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
}
