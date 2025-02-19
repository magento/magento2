<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Mail;

use Magento\Framework\Mail\AddressFactory;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Message as SymfonyMessage;
use Symfony\Component\Mime\Address as SymfonyAddress;

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
     * @return \Magento\Framework\Mail\EmailMessageInterface
     */
    public function fromString(string $content): \Magento\Framework\Mail\EmailMessageInterface
    {
        $part = new DataPart($content);
        $part->setDisposition('inline');
        $symfonyMimeMessage = new SymfonyMessage(null, $part);
        if ($symfonyMimeMessage->getBody() instanceof AbstractPart &&
            method_exists($symfonyMimeMessage->getBody(), 'getFilename')) {
            $filename = $symfonyMimeMessage->getBody()->getFilename();
        } else {
            $filename = '';
        }

        $mimePart = [];

            /** @var \Magento\Framework\Mail\MimePartInterface $mimePart */
        $mimePart = $this->mimePartInterfaceFactory->create(
            [
                'content' => $symfonyMimeMessage->getBody()->toString(),
                'type' => 'text/' . $symfonyMimeMessage->getBody()->getMediaSubtype(),
                'fileName' => $filename,
                'disposition' => $symfonyMimeMessage->getBody()->getDisposition(),
                'encoding' => $symfonyMimeMessage->getHeaders()->getHeaderBody('Content-Transfer-Encoding'),
                'description' => $symfonyMimeMessage->getHeaders()->getHeaderBody('Content-Description'),
                'filters' => [],
                'charset' => $symfonyMimeMessage->getHeaders()->get('Content-Type')?->getCharset(),
                'boundary' => $symfonyMimeMessage->getHeaders()->getHeaderParameter('Content-Type', 'boundary'),
                'location' => $symfonyMimeMessage->getHeaders()->getHeaderBody('Content-Location'),
                'language' => $symfonyMimeMessage->getHeaders()->getHeaderBody('Content-Language'),
                'isStream' => is_resource($symfonyMimeMessage->getBody())
            ]
        );

        $body = $this->mimeMessageInterfaceFactory->create([
            'parts' => [$mimePart]
        ]);

        $sender = $symfonyMimeMessage->getHeaders()->get('Sender') ? $this->addressFactory->create([
            'email' => $symfonyMimeMessage->getHeaders()->get('Sender')->getAddress(),
            'name' => $symfonyMimeMessage->getHeaders()->get('Sender')->getName()
        ]): $this->addressFactory->create([
            'email' => 'sender@example.com',
            'name' => 'Sender'
        ]);

        $address = [
            'email' => 'john@example.com',
            'name' => 'John'
        ];

        return $this->emailMessageInterfaceFactory->create([
            'body' => $body,
            'subject' => $symfonyMimeMessage->getHeaders()->getHeaderBody('Subject'),
            'sender' => $sender,
            'to' => $this->convertAddresses(
                $symfonyMimeMessage->getHeaders()->get('To')?->getAddresses()
                    ?? $address
            ),
            'from' => $this->convertAddresses(
                $symfonyMimeMessage->getHeaders()->get('From')?->getAddresses()
                    ?? $address
            ),
            'cc' => $this->convertAddresses(
                $symfonyMimeMessage->getHeaders()->get('Cc')?->getAddresses()
                    ?? $address
            ),
            'bcc' => $this->convertAddresses(
                $symfonyMimeMessage->getHeaders()->get('Bcc')?->getAddresses()
                    ?? $address
            ),
            'replyTo' => $this->convertAddresses(
                $symfonyMimeMessage->getHeaders()->get('Reply-To')?->getAddresses()
                    ?? $address
            ),
        ]);
    }

    /**
     * Convert addresses to internal mail addresses
     *
     * @param array $address
     * @return array
     */
    private function convertAddresses(array $address): array
    {
        return [
            $this->addressFactory->create([
            'email' => $address['email'],
            'name' => $address['name']
            ])
        ];
    }
}
