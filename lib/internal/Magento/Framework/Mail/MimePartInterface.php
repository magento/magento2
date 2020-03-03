<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mail;

/**
 * Interface representing a MIME part.
 */
interface MimePartInterface
{
    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding(): string;

    /**
     * Get disposition
     *
     * @return string
     */
    public function getDisposition(): string;

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get filename
     *
     * @return string
     */
    public function getFileName(): string;

    /**
     * Get charset
     *
     * @return string
     */
    public function getCharset(): string;

    /**
     * Get boundary
     *
     * @return string
     */
    public function getBoundary(): string;

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation(): string;

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage(): string;

    /**
     * Get Filters
     *
     * @return array
     */
    public function getFilters(): array;

    /**
     * Check if this part can be read as a stream
     *
     * @return bool
     */
    public function isStream(): bool;

    /**
     * If this was created with a stream, return a filtered stream for reading the content. Useful for file attachment
     *
     * @param string $endOfLine
     *
     * @return resource
     */
    public function getEncodedStream($endOfLine = MimeInterface::LINE_END);

    /**
     * Get the Content of the current Mime Part in the given encoding.
     *
     * @param string $endOfLine
     *
     * @return string|resource
     */
    public function getContent($endOfLine = MimeInterface::LINE_END);

    /**
     * Get the RAW unencoded content from this part
     *
     * @return string
     */
    public function getRawContent(): string;

    /**
     * Create and return the array of headers for this MIME part
     *
     * @param string $endOfLine
     *
     * @return array
     */
    public function getHeadersArray($endOfLine = MimeInterface::LINE_END): array;

    /**
     * Create and return the array of headers for this MIME part
     *
     * @param string $endOfLine
     *
     * @return string
     */
    public function getHeaders($endOfLine = MimeInterface::LINE_END): string;
}
