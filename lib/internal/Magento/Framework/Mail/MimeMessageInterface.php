<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mail;

/**
 * Interface MimeMessageInterface
 */
interface MimeMessageInterface
{
    /**
     * Returns the list of all MimeParts in the message
     *
     * @return MimePartInterface[]
     */
    public function getParts(): array;

    /**
     * Check if message needs to be sent as multipart MIME message or if it has only one part.
     *
     * @return bool
     */
    public function isMultiPart(): bool;

    /**
     * Generate MIME-compliant message from the current configuration
     *
     * @param string $endOfLine
     *
     * @return string
     */
    public function getMessage(string $endOfLine = MimeInterface::LINE_END): string;

    /**
     * Get the headers of a given part as an array
     *
     * @param int $partNum
     *
     * @return array
     */
    public function getPartHeadersAsArray(int $partNum): array;

    /**
     * Get the headers of a given part as a string
     *
     * @param int $partNum
     * @param string $endOfLine
     *
     * @return string
     */
    public function getPartHeaders(int $partNum, string $endOfLine = MimeInterface::LINE_END): string;

    /**
     * Get the (encoded) content of a given part as a string
     *
     * @param int $partNum
     * @param string $endOfLine
     *
     * @return string
     */
    public function getPartContent(int $partNum, string $endOfLine = MimeInterface::LINE_END): string;
}
