<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Bulk;

use Magento\Framework\MessageQueue\Bulk\ExchangeInterface;
use Magento\Framework\MessageQueue\MessageDeliveryMode;
use Magento\Framework\MessageQueue\QueueResolver;
use Magento\Framework\Stomp\StompClientInterface;
use Stomp\Transport\Message;

class Exchange implements ExchangeInterface
{
    /**
     * @param StompClientInterface $stompClient
     * @param QueueResolver $queueResolver
     */
    public function __construct(
        private readonly StompClientInterface $stompClient,
        private readonly QueueResolver $queueResolver,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function enqueue($topic, array $envelopes)
    {
        $messages = [];
        foreach ($envelopes as $envelope) {
            $headers = $envelope->getProperties();
            $deliveryMode = $headers['delivery_mode'] ?? null;
            if (MessageDeliveryMode::PERSISTENT->value === $deliveryMode) {
                $headers['persistent'] = 'true';
            }
            $headers['destination-type'] = 'ANYCAST';
            $messages[] = new Message($envelope->getBody(), $headers);
        }
        $queue = $this->queueResolver->getByTopic($topic);
        $this->stompClient->sendBatch($queue, $messages);

        return null;
    }
}
