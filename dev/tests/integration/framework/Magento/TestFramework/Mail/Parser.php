<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Mail;

use Magento\Framework\Mail\AddressInterface;
use Magento\Framework\Mail\AddressFactory;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;

class Parser
{
    /**
     * @var EmailMessageInterfaceFactory
     */
    private EmailMessageInterfaceFactory $emailMessageInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private MimeMessageInterfaceFactory $mimeMessageInterfaceFactory;

    /**
     * @var MimePartInterfaceFactory
     */
    private MimePartInterfaceFactory $mimePartInterfaceFactory;

    /**
     * @var AddressFactory
     */
    private AddressFactory $addressFactory;

    /**
     * @param EmailMessageInterfaceFactory $emailMessageInterfaceFactory
     * @param MimeMessageInterfaceFactory $mimeMessageInterfaceFactory
     * @param MimePartInterfaceFactory $mimePartInterfaceFactory
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory,
        MimePartInterfaceFactory $mimePartInterfaceFactory,
        AddressFactory $addressFactory
    ) {

        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory;
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory;
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * Parses mail string into EmailMessage
     *
     * @param string $content
     * @return EmailMessageInterface
     */
    public function fromString(string $content): EmailMessageInterface
    {
        $parts = preg_split('/\r?\n\r?\n/', $content, 2);
        $headerText = $parts[0] ?? '';
        $bodyText = $parts[1] ?? '';
        $headers = $this->parseHeaders($headerText);
        $contentType = $headers['Content-Type'] ?? 'text/plain';
        $charset = $this->extractParameter($contentType, 'charset') ?? 'utf-8';
        $boundary = $this->extractParameter($contentType, 'boundary');
        $encoding = $headers['Content-Transfer-Encoding'] ?? 'quoted-printable';
        $disposition = $headers['Content-Disposition'] ?? 'inline';
        $decodedBody = match (strtolower($encoding)) {
            'base64' => base64_decode($bodyText),
            'quoted-printable' => quoted_printable_decode($bodyText),
            default => $bodyText,
        };

        $mimePart = $this->mimePartInterfaceFactory->create([
            'content' => $decodedBody,
            'type' => strtok($contentType, ';'),
            'fileName' => '',
            'disposition' => $disposition,
            'encoding' => $encoding,
            'description' => $headers['Content-Description'] ?? '',
            'filters' => [],
            'charset' => $charset,
            'boundary' => $boundary,
            'location' => $headers['Content-Location'] ?? '',
            'language' => $headers['Content-Language'] ?? '',
            'isStream' => false
        ]);

        $mimeMessage = $this->mimeMessageInterfaceFactory->create([
            'parts' => [$mimePart]
        ]);

        $to = $this->parseAddresses($headers['To'] ?? '');
        $from = $this->parseAddresses($headers['From'] ?? '');
        $cc = $this->parseAddresses($headers['Cc'] ?? '');
        $bcc = $this->parseAddresses($headers['Bcc'] ?? '');
        $replyTo = $this->parseAddresses($headers['Reply-To'] ?? '');

        $sender = null;
        if (!empty($headers['Sender'])) {
            $senderAddresses = $this->parseAddresses($headers['Sender']);
            $sender = $senderAddresses[0] ?? null;
        } elseif (!empty($from)) {
            $sender = $from[0];
        }

        return $this->emailMessageInterfaceFactory->create([
            'body' => $mimeMessage,
            'subject' => $headers['Subject'] ?? '',
            'sender' => $sender,
            'to' => $to,
            'from' => $from,
            'cc' => $cc,
            'bcc' => $bcc,
            'replyTo' => $replyTo,
        ]);
    }

    /**
     * Parse email headers from string more efficiently
     *
     * @param string $headerText
     * @return array<string, string>
     */
    private function parseHeaders(string $headerText): array
    {
        if (empty($headerText)) {
            return [];
        }

        $headers = [];
        $lines = preg_split('/\r?\n/', $headerText);
        $currentHeader = '';

        foreach ($lines as $line) {
            if (preg_match('/^\s+(.+)$/', $line, $matches)) {
                if ($currentHeader !== '') {
                    $headers[$currentHeader] .= ' ' . trim($matches[1]);
                }
                continue;
            }
            if (preg_match('/^([^:]+):\s*(.*)$/', $line, $matches)) {
                $currentHeader = $matches[1];
                $headers[$currentHeader] = trim($matches[2]);
            }
        }

        return $headers;
    }

    /**
     * Parse email addresses from string
     *
     * @param string $addressString
     * @return array<AddressInterface>
     */
    private function parseAddresses(string $addressString): array
    {
        if (empty($addressString)) {
            return [];
        }

        $addresses = [];
        $addressParts = explode(',', $addressString);

        foreach ($addressParts as $addressPart) {
            $addressPart = trim($addressPart);
            if (preg_match('/^(?:"?([^"]*)"?\s*)?<?([^>]*)>?$/', $addressPart, $matches)) {
                $name = trim($matches[1]);
                $email = trim($matches[2]);

                if (empty($email) && filter_var($matches[1], FILTER_VALIDATE_EMAIL)) {
                    $email = $matches[1];
                    $name = '';
                }

                if (!empty($email)) {
                    $addresses[] = $this->addressFactory->create([
                        'email' => $email,
                        'name' => $name
                    ]);
                }
            }
        }

        return $addresses;
    }

    /**
     * Extract parameter value from a header that contains parameters (like Content-Type)s
     *
     * @param string $header
     * @param string $paramName
     * @return string|null
     */
    private function extractParameter(string $header, string $paramName): ?string
    {
        if (preg_match('/\b' . preg_quote($paramName) . '=(["\']?)([^"\';\s]+)\1/i', $header, $matches)) {
            return $matches[2];
        }

        return null;
    }
}
