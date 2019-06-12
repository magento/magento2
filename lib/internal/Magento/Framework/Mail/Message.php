<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

use Zend\Mime\Mime;
use Zend\Mime\Part;

<<<<<<< HEAD
=======
/**
 * Class Message for email transportation
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
     * {@inheritdoc}
=======
     * @inheritdoc
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
     * {@inheritdoc}
=======
     * @inheritdoc
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
     */
    public function setFrom($fromAddress)
    {
        $this->zendMessage->setFrom($fromAddress);
=======
     *
     * @deprecated This function is missing the from name. The
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);
<<<<<<< HEAD
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $this;
    }

    /**
     * @inheritdoc
     */
<<<<<<< HEAD
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
=======
    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    /**
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
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
<<<<<<< HEAD

    /**
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
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
}
