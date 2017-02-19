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

class Message extends \Zend\Mail\Message implements MessageInterface
{
    /**
     * @param string $charset
     */
    public function __construct($charset = 'utf-8')
    {
        parent::setEncoding($charset);
    }

    /**
     * Message type
     *
     * @var string
     */
    protected $messageType = self::TYPE_TEXT;

    private function htmlMimeFromString($htmlBody)
    {
        $htmlPart = new Part($htmlBody);
        $htmlPart->setCharset($this->getEncoding());
        $htmlPart->setType(Mime::TYPE_HTML);
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart($htmlPart);
        return $mimeMessage;
    }

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

}
