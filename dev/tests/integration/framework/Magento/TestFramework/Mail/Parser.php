<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Mail;

use Magento\Framework\Mail\AddressFactory;
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
     * @return \Magento\Framework\Mail\EmailMessageInterface
     */
    public function fromString(string $content): \Magento\Framework\Mail\EmailMessageInterface
    {
        $laminasMessage = \Laminas\Mail\Message::fromString($content)->setEncoding('utf-8');
        $laminasMimeMessage = is_string($laminasMessage->getBody())
            ? \Laminas\Mime\Message::createFromMessage($content)
            : $laminasMessage->getBody();

        $mimeParts = [];

        foreach ($laminasMimeMessage->getParts() as $laminasMimePart) {
            /** @var \Magento\Framework\Mail\MimePartInterface $mimePart */
            $mimeParts[] = $this->mimePartInterfaceFactory->create(
                [
                    'content' => $laminasMimePart->getRawContent(),
                    'type' => $laminasMimePart->getType(),
                    'fileName' => $laminasMimePart->getFileName(),
                    'disposition' => $laminasMimePart->getDisposition(),
                    'encoding' => $laminasMimePart->getEncoding(),
                    'description' => $laminasMimePart->getDescription(),
                    'filters' => $laminasMimePart->getFilters(),
                    'charset' => $laminasMimePart->getCharset(),
                    'boundary' => $laminasMimePart->getBoundary(),
                    'location' => $laminasMimePart->getLocation(),
                    'language' => $laminasMimePart->getLocation(),
                    'isStream' => $laminasMimePart->isStream()
                ]
            );
        }

        $body = $this->mimeMessageInterfaceFactory->create([
            'parts' => $mimeParts
        ]);

        $sender = $laminasMessage->getSender() ? $this->addressFactory->create([
            'email' => $laminasMessage->getSender()->getEmail(),
            'name' => $laminasMessage->getSender()->getName()
        ]): null;

        return $this->emailMessageInterfaceFactory->create([
            'body' => $body,
            'subject' => $laminasMessage->getSubject(),
            'sender' => $sender,
            'to' => $this->convertAddresses($laminasMessage->getTo()),
            'from' => $this->convertAddresses($laminasMessage->getFrom()),
            'cc' => $this->convertAddresses($laminasMessage->getCc()),
            'bcc' => $this->convertAddresses($laminasMessage->getBcc()),
            'replyTo' => $this->convertAddresses($laminasMessage->getReplyTo()),
        ]);
    }

    /**
     * Convert laminas addresses to internal mail addresses
     *
     * @param \Laminas\Mail\AddressList $addressList
     * @return array
     */
    private function convertAddresses(\Laminas\Mail\AddressList $addressList): array
    {
        $addresses = [];
        foreach ($addressList as $address) {
            $addresses[] = $this->addressFactory->create([
                'email' => $address->getEmail(),
                'name' => $address->getName()
            ]);
        }
        return $addresses;
    }
}
