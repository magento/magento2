<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

/**
 * Mail Message interface
 *
 * @api
 * @since 2.0.0
 */
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
     * @since 2.0.0
     */
    public function setSubject($subject);

    /**
     * Get message subject
     *
     * @return string
     * @since 2.0.0
     */
    public function getSubject();

    /**
     * Set message body
     *
     * @param mixed $body
     * @return $this
     * @since 2.0.0
     */
    public function setBody($body);

    /**
     * Get message body
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getBody();

    /**
     * Set from address
     *
     * @param string|array $fromAddress
     * @return $this
     * @since 2.0.0
     */
    public function setFrom($fromAddress);

    /**
     * Add to address
     *
     * @param string|array $toAddress
     * @return $this
     * @since 2.0.0
     */
    public function addTo($toAddress);

    /**
     * Add cc address
     *
     * @param string|array $ccAddress
     * @return $this
     * @since 2.0.0
     */
    public function addCc($ccAddress);

    /**
     * Add bcc address
     *
     * @param string|array $bccAddress
     * @return $this
     * @since 2.0.0
     */
    public function addBcc($bccAddress);

    /**
     * Set reply-to address
     *
     * @param string|array $replyToAddress
     * @return $this
     * @since 2.0.0
     */
    public function setReplyTo($replyToAddress);

    /**
     * Set message type
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setMessageType($type);
}
