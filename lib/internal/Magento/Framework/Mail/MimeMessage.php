<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\Part\AbstractMultipartPart;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Symfony\Component\Mime\Part\TextPart;

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
     * @throws InvalidArgumentException
     */
    public function __construct(array $parts)
    {
        $headers = null;
        $body = null;
        $attachments = [];

        foreach ($parts as $part) {
            $mimePart = $part->getMimePart();
            if ($mimePart instanceof DataPart) {
                $attachments[] = $mimePart;
            } elseif ($mimePart instanceof TextPart && $body === null) {
                $headers = $mimePart->getHeaders();
                $body = $mimePart;
            }
        }

        if ($attachments) {
            try {
                $body = $body !== null ? new MixedPart($body, ...$attachments) : new MixedPart(...$attachments);
            } catch (\Exception $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
            $headers = null;
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

        if ($body instanceof MixedPart) {
            foreach ($body->getParts() as $part) {
                if ($part instanceof AlternativePart) {
                    array_push($parts, ...$part->getParts());
                    continue;
                }
                $parts[] = $part;
            }
        } elseif ($body instanceof AlternativePart) {
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
        return $body instanceof AbstractMultipartPart && count($body->getParts()) > 1;
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
