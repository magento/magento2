<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

/**
 * Mail Message interface
 *
 * @api
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

    /**
     * Creates a \Zend_Mime_Part attachment
     *
     * Attachment is automatically added to the mail object after creation. The
     * attachment object is returned to allow for further manipulation.
     *
     * @param  string $body
     * @param  string $mimeType
     * @param  string $disposition
     * @param  string $encoding
     * @param  string $filename OPTIONAL A filename for the attachment
     * @return \Zend_Mime_Part Newly created \Zend_Mime_Part object (to allow
     * advanced settings)
     */
    public function createAttachment(
        $body,
        $mimeType = \Zend_Mime::TYPE_OCTETSTREAM,
        $disposition = \Zend_Mime::DISPOSITION_ATTACHMENT,
        $encoding = \Zend_Mime::ENCODING_BASE64,
        $filename = null);
}
