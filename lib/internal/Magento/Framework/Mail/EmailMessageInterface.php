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
     * @return Address[]|null
     */
    public function getFrom(): ?array;

    /**
     * Access the address list of the To header
     *
     * @return Address[]
     */
    public function getTo(): array;

    /**
     * Retrieve list of CC recipients
     *
     * @return Address[]|null
     */
    public function getCc(): ?array;

    /**
     * Retrieve list of Bcc recipients
     *
     * @return Address[]|null
     */
    public function getBcc(): ?array;

    /**
     * Access the address list of the Reply-To header
     *
     * @return Address[]|null
     */
    public function getReplyTo(): ?array;

    /**
     * Retrieve the sender address, if any
     *
     * @return Address|null
     */
    public function getSender(): ?Address;

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
