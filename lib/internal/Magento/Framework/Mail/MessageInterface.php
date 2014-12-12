<?php
/**
 * Mail Message interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Mail;

interface MessageInterface
{
    /**
     * Types of message
     */
    const TYPE_TEXT = 'text/plain';

    const TYPE_HTML = 'text/html';

    /**
     * Set message subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject);

    /**
     * Get message subject
     *
     * @return string
     */
    public function getSubject();

    /**
     * Set message body
     *
     * @param mixed $body
     * @return $this
     */
    public function setBody($body);

    /**
     * Get message body
     *
     * @return mixed
     */
    public function getBody();

    /**
     * Set from address
     *
     * @param string|array $fromAddress
     * @return $this
     */
    public function setFrom($fromAddress);

    /**
     * Add to address
     *
     * @param string|array $toAddress
     * @return $this
     */
    public function addTo($toAddress);

    /**
     * Add cc address
     *
     * @param string|array $ccAddress
     * @return $this
     */
    public function addCc($ccAddress);

    /**
     * Add bcc address
     *
     * @param string|array $bccAddress
     * @return $this
     */
    public function addBcc($bccAddress);

    /**
     * Set reply-to address
     *
     * @param string|array $replyToAddress
     * @return $this
     */
    public function setReplyTo($replyToAddress);

    /**
     * Set message type
     *
     * @param string $type
     * @return $this
     */
    public function setMessageType($type);
}
