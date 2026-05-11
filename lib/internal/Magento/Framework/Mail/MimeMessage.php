<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;

/**
 * Magento Framework Mime message
 */
class MimeMessage implements MimeMessageInterface
{
    /**
     * @var Message
     */
    private $mimeMessage;

    /**
     * MimeMessage constructor
     *
     * @param array $parts
     */
    public function __construct(array $parts)
    {
        $headers = null;
        $body = null;

        foreach ($parts as $part) {
            $mimePart = $part->getMimePart();
            if ($mimePart instanceof TextPart) {
                $headers = $mimePart->getHeaders();
                $body = $mimePart;
                break;
            }
        }

        $this->mimeMessage = new Message($headers, $body);
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        $parts = [];
        $body = $this->mimeMessage->getBody();

        if ($body instanceof AlternativePart) {
            $parts = $body->getParts();
        } elseif ($body instanceof TextPart) {
            $parts[] = $body;
        }

        return $parts;
    }

    /**
     * @inheritDoc
     */
    public function isMultiPart(): bool
    {
        $body = $this->mimeMessage->getBody();
        return $body instanceof AlternativePart && $body->countParts() > 1;
    }

    /**
     * @inheritDoc
     */
    public function getMessage(string $endOfLine = MimeInterface::LINE_END): string
    {
        return str_replace("\r\n", $endOfLine, $this->mimeMessage->toString());
    }

    /**
     * @inheritDoc
     */
    public function getPartHeadersAsArray(int $partNum): array
    {
        $parts = $this->getParts();
        if (isset($parts[$partNum])) {
            $headersArray = [];
            foreach ($parts[$partNum]->getHeaders()->toArray() as $header) {
                $headersArray[$header->getName()] = $header->getBodyAsString();
            }
            return $headersArray;
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getPartHeaders(int $partNum, string $endOfLine = MimeInterface::LINE_END): string
    {
        $parts = $this->getParts();
        if (isset($parts[$partNum])) {
            $headers = $parts[$partNum]->getHeaders();
            $headersString = $headers->toString();

            return str_replace("\r\n", $endOfLine, $headersString);
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getPartContent(int $partNum, string $endOfLine = MimeInterface::LINE_END): string
    {
        $parts = $this->getParts();
        if (isset($parts[$partNum])) {
            $content = $parts[$partNum]->getBodyAsString();

            return str_replace("\r\n", $endOfLine, $content);
        }

        return '';
    }

    /**
     * Get Mime Message Object
     *
     * @return Message
     */
    public function getMimeMessage(): Message
    {
        return $this->mimeMessage;
    }
}
