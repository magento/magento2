<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\TextPart;

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
     * @var TextPart | DataPart
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws InvalidArgumentException
     */
    public function __construct(
        $content,
        ?string $type = MimeInterface::TYPE_HTML,
        ?string $fileName = null,
        ?string $disposition = MimeInterface::DISPOSITION_INLINE,
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
            if ($type === MimeInterface::TYPE_HTML) {
                $this->mimePart = new TextPart($content, $charset, 'html', $encoding);
            } elseif ($type === MimeInterface::TYPE_TEXT) {
                $this->mimePart = new TextPart($content, $charset, 'plain', $encoding);
            } else {
                $this->mimePart = new DataPart($content, $fileName, $type, $encoding);
            }
        } catch (\Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
        if ($boundary) {
            $contentTypeHeader = $this->mimePart->getHeaders()->get('Content-Type');
            if ($contentTypeHeader) {
                $contentTypeHeader->setParameter('boundary', $boundary);
            }
        }

        if ($disposition) {
            $this->mimePart->setDisposition($disposition);
        }
        if ($description) {
            $this->mimePart->getHeaders()->addTextHeader('Content-Description', $description);
        }
        if ($location) {
            $this->mimePart->getHeaders()->addTextHeader('Content-Location', $location);
        }
        if ($language) {
            $this->mimePart->getHeaders()->addTextHeader('Content-Language', $language);
        }
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->mimePart->getMediaSubtype();
    }

    /**
     * @inheritDoc
     */
    public function getEncoding(): string
    {
        return $this->mimePart->getHeaders()->getHeaderBody('Content-Transfer-Encoding');
    }

    /**
     * @inheritDoc
     */
    public function getDisposition(): string
    {
        return $this->mimePart->getDisposition() ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->mimePart->getHeaders()->getHeaderBody('Content-Description') ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getFileName(): string
    {
        return $this->mimePart->getFileName() ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getCharset(): string
    {
        $contentTypeHeader = $this->mimePart->getHeaders()->get('Content-Type');
        return $contentTypeHeader ? $contentTypeHeader->getCharset() : '';
    }

    /**
     * @inheritDoc
     */
    public function getBoundary(): string
    {
        return $this->mimePart->getHeaders()->getHeaderParameter('Content-Type', 'boundary') ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getLocation(): string
    {
        return $this->mimePart->getHeaders()->getHeaderBody('Content-Location') ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(): string
    {
        return $this->mimePart->getHeaders()->getHeaderBody('Content-Language') ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function isStream(): bool
    {
        return is_resource($this->mimePart->getBody());
    }

    /**
     * @inheritDoc
     */
    public function getEncodedStream($endOfLine = MimeInterface::LINE_END)
    {
        if (!$this->isStream()) {
            return null;
        }

        try {
            $stream = $this->mimePart->getBody();
            switch ($this->getEncoding()) {
                case MimeInterface::ENCODING_QUOTED_PRINTABLE:
                    $filter = stream_filter_append(
                        $stream,
                        'convert.quoted-printable-encode',
                        STREAM_FILTER_READ,
                        [
                            'line-length' => MimeInterface::LINE_LENGTH,
                            'line-break-chars' => $endOfLine,
                        ]
                    );
                    if (!is_resource($filter)) {
                        throw new InvalidArgumentException('Failed to append quoted-printable filter');
                    }
                    break;
                case MimeInterface::ENCODING_BASE64:
                    $filter = stream_filter_append(
                        $stream,
                        'convert.base64-encode',
                        STREAM_FILTER_READ,
                        [
                            'line-length' => MimeInterface::LINE_LENGTH,
                            'line-break-chars' => $endOfLine,
                        ]
                    );
                    if (!is_resource($filter)) {
                        throw new InvalidArgumentException('Failed to append base64 filter');
                    }
                    break;
            }
        } catch (\Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        return $stream;
    }

    /**
     * @inheritDoc
     */
    public function getContent($endOfLine = MimeInterface::LINE_END)
    {
        try {
            if ($this->isStream()) {
                return stream_get_contents($this->getEncodedStream($endOfLine));
            }
        } catch (\Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        return $this->mimePart->getBodyAsString();
    }

    /**
     * @inheritDoc
     */
    public function getRawContent(): string
    {
        try {
            if ($this->isStream()) {
                return stream_get_contents($this->mimePart->getBody());
            }
        } catch (\Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        return $this->mimePart->getBody();
    }

    /**
     * @inheritDoc
     */
    public function getHeadersArray($endOfLine = MimeInterface::LINE_END): array
    {
        return $this->mimePart->getPreparedHeaders()->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getHeaders($endOfLine = MimeInterface::LINE_END): string
    {
        $headers = $this->mimePart->getHeaders();
        $headersString = $headers->toString();

        return str_replace("\r\n", $endOfLine, $headersString);
    }

    /**
     * Get the MimePart object
     *
     * @return TextPart | DataPart
     */
    public function getMimePart(): TextPart | DataPart
    {
        return $this->mimePart;
    }
}
