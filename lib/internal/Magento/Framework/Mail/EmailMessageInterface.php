<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mail;

/**
 * Interface EmailMessageInterface
 */
interface EmailMessageInterface
{
    /**
     * Get the message encoding
     *
     * @return string
     */
    public function getEncoding(): string;

    /**
     * Access headers collection
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Retrieve list of From senders
     *
     * @return MailAddressList|null
     */
    public function getFrom(): ?MailAddressList;

    /**
     * Access the address list of the To header
     *
     * @return MailAddressList
     */
    public function getTo(): MailAddressList;

    /**
     * Retrieve list of CC recipients
     *
     * @return MailAddressList|null
     */
    public function getCc(): ?MailAddressList;

    /**
     * Retrieve list of BCC recipients
     *
     * @return MailAddressList|null
     */
    public function getBcc(): ?MailAddressList;

    /**
     * Access the address list of the Reply-To header
     *
     * @return MailAddressList|null
     */
    public function getReplyTo(): ?MailAddressList;

    /**
     * Retrieve the sender address, if any
     *
     * @return MailAddress|null
     */
    public function getSender(): ?MailAddress;

    /**
     * Get the message subject header value
     *
     * @return null|string
     */
    public function getSubject(): ?string;

    /**
     * Return the currently set message body
     *
     * @return MimeMessageInterface
     */
    public function getBody(): MimeMessageInterface;

    /**
     * Get the string-serialized message body text
     *
     * @return string
     */
    public function getBodyText(): string;

    /**
     * Serialize to string
     *
     * @return string
     */
    public function toString(): string;
}
