<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

/**
 * Class MailEnvelopeBuilder
 */
class MailEnvelopeBuilder
{
    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageFactory;

    /**
     * @var MessageEnvelopeInterfaceFactory
     */
    private $messageEnvelopeFactory;

    /**
     * @var MimePartInterfaceFactory
     */
    private $mimePartFactory;

    /**
     * MailEnvelopeBuilder constructor
     *
     * @param MessageEnvelopeInterfaceFactory $messageEnvelopeFactory
     * @param MimeMessageInterfaceFactory $mimeMessageFactory
     * @param MimePartInterfaceFactory $mimePartFactory
     */
    public function __construct(
        MessageEnvelopeInterfaceFactory $messageEnvelopeFactory,
        MimeMessageInterfaceFactory $mimeMessageFactory,
        MimePartInterfaceFactory $mimePartFactory
    ) {
        $this->messageEnvelopeFactory = $messageEnvelopeFactory;
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->mimePartFactory = $mimePartFactory;
    }

    /**
     * Builds message Envelope from Message Data array
     *
     * @param array $messageData
     * @return MessageEnvelopeInterface
     */
    public function buildByArray(array $messageData): MessageEnvelopeInterface
    {
        $parts = [];
        if (isset($messageData['body'])) {
            foreach ($messageData['body'] as $item) {
                if ($item instanceof MimePartInterface) {
                    $parts[] = $item;
                } else {
                    $parts[] = $this->mimePartFactory->create($item);
                }
            }
        }
        $messageData['body'] = $this->mimeMessageFactory->create(['parts'=>$parts]);

        return $this->messageEnvelopeFactory->create($messageData);
    }
}
