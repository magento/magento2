<?php
/**
 * Mail Message
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

use Zend\Mime\Mime;
use Zend\Mime\Part;

/**
 * @todo composition instead of inheritance for better testability
 * - add a ZendMailDecorator interface with getZendMail() method for usage in \Magento\Framework\Mail\Transport
 * @todo get rid of temporal coupling (setMessageType() + setBody())
 * - deprecate setMessageType(), implement a HtmlMessage decorator instead
 * - change usage in \Magento\Framework\Mail\Template\TransportBuilder::prepareMessage()
 */
class Message extends \Zend\Mail\Message implements MessageInterface
{

    /**
     * @param string $charset
     */
    public function __construct($charset = 'utf-8')
    {
        $this->encoding = $charset;
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
     * @return \Zend\Mail\Message
     */
    public function setBody($body)
    {
        if (is_string($body) && $this->messageType === MessageInterface::TYPE_HTML) {
            $body = self::htmlMimeFromString($body);
        }
        return parent::setBody($body);
    }

    /**
     * @param string $htmlBody
     * @return \Zend\Mime\Message
     */
    private function htmlMimeFromString($htmlBody)
    {
        $htmlPart = new Part($htmlBody);
        $htmlPart->setCharset($this->getEncoding());
        $htmlPart->setType(Mime::TYPE_HTML);
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart($htmlPart);
        return $mimeMessage;
    }
}
