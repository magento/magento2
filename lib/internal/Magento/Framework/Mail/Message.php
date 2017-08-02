<?php
/**
 * Mail Message
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

/**
 * Class \Magento\Framework\Mail\Message
 *
 * @since 2.0.0
 */
class Message extends \Zend_Mail implements MessageInterface
{
    /**
     * @param string $charset
     * @since 2.0.0
     */
    public function __construct($charset = 'utf-8')
    {
        parent::__construct($charset);
    }

    /**
     * Message type
     *
     * @var string
     * @since 2.0.0
     */
    protected $messageType = self::TYPE_TEXT;

    /**
     * Set message body
     *
     * @param string $body
     * @return $this
     * @since 2.0.0
     */
    public function setBody($body)
    {
        return $this->messageType == self::TYPE_TEXT ? $this->setBodyText($body) : $this->setBodyHtml($body);
    }

    /**
     * Set message body
     *
     * @return string
     * @since 2.0.0
     */
    public function getBody()
    {
        return $this->messageType == self::TYPE_TEXT ? $this->getBodyText() : $this->getBodyHtml();
    }

    /**
     * Set message type
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setMessageType($type)
    {
        $this->messageType = $type;
        return $this;
    }
}
