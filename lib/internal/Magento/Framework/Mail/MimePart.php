<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Zend\Mime\Part as ZendMimePart;

/**
 * @inheritDoc
 */
class MimePart implements MimePartInterface
{
    /**
     * UTF-8 charset
     */
    public const CHARSET_UTF8 = 'utf-8';

    /**
     * @var ZendMimePart
     */
    private $mimePart;

    /**
     * MimePart constructor
     *
     * @param resource|string $content
     * @param string|null $type
     * @param string|null $fileName
     * @param string|null $disposition
     * @param string|null $encoding
     * @param string|null $description
     * @param array|null $filters
     * @param string|null $charset
     * @param string|null $boundary
     * @param string|null $location
     * @param string|null $language
     * @param bool|null $isStream
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws InvalidArgumentException
     */
    public function __construct(
        $content,
        ?string $type = MimeInterface::TYPE_HTML,
        ?string $fileName = null,
        ?string $disposition = null,
        ?string $encoding = MimeInterface::ENCODING_QUOTED_PRINTABLE,
        ?string $description = null,
        ?array $filters = [],
        ?string $charset = self::CHARSET_UTF8,
        ?string $boundary = null,
        ?string $location = null,
        ?string $language = null,
        ?bool $isStream = null
    ) {
        try {
            $this->mimePart = new ZendMimePart($content);
        } catch (\Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
        $this->mimePart->setType($type);
        $this->mimePart->setEncoding($encoding);
        $this->mimePart->setFilters($filters);
        if ($charset) {
            $this->mimePart->setBoundary($boundary);
        }
        if ($charset) {
            $this->mimePart->setCharset($charset);
        }
        if ($disposition) {
            $this->mimePart->setDisposition($disposition);
        }
        if ($description) {
            $this->mimePart->setDescription($description);
        }
        if ($fileName) {
            $this->mimePart->setFileName($fileName);
        }
        if ($location) {
            $this->mimePart->setLocation($location);
        }
        if ($language) {
            $this->mimePart->setLanguage($language);
        }
        if ($isStream) {
            $this->mimePart->setIsStream($isStream);
        }
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->mimePart->getType();
    }

    /**
     * @inheritDoc
     */
    public function getEncoding(): string
    {
        return $this->mimePart->getEncoding();
    }

    /**
     * @inheritDoc
     */
    public function getDisposition(): string
    {
        return $this->mimePart->getDisposition();
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->mimePart->getDescription();
    }

    /**
     * @inheritDoc
     */
    public function getFileName(): string
    {
        return $this->mimePart->getFileName();
    }

    /**
     * @inheritDoc
     */
    public function getCharset(): string
    {
        return $this->mimePart->getCharset();
    }

    /**
     * @inheritDoc
     */
    public function getBoundary(): string
    {
        return $this->mimePart->getBoundary();
    }

    /**
     * @inheritDoc
     */
    public function getLocation(): string
    {
        return $this->mimePart->getLocation();
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(): string
    {
        return $this->mimePart->getLanguage();
    }

    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return $this->mimePart->getFilters();
    }

    /**
     * @inheritDoc
     */
    public function isStream(): bool
    {
        return $this->mimePart->isStream();
    }

    /**
     * @inheritDoc
     */
    public function getEncodedStream($endOfLine = MimeInterface::LINE_END)
    {
        return $this->mimePart->getEncodedStream($endOfLine);
    }

    /**
     * @inheritDoc
     */
    public function getContent($endOfLine = MimeInterface::LINE_END)
    {
        return $this->mimePart->getContent($endOfLine);
    }

    /**
     * @inheritDoc
     */
    public function getRawContent(): string
    {
        return $this->mimePart->getRawContent();
    }

    /**
     * @inheritDoc
     */
    public function getHeadersArray($endOfLine = MimeInterface::LINE_END): array
    {
        return $this->mimePart->getHeadersArray($endOfLine);
    }

    /**
     * @inheritDoc
     */
    public function getHeaders($endOfLine = MimeInterface::LINE_END): string
    {
        return $this->mimePart->getHeaders($endOfLine);
    }
}
