<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\Config\Data as MessageQueueConfig;

/**
 * A RabbitMQ Publisher to handle publishing a message.
 */
class Publisher implements PublisherInterface
{
    /**
     * @var ExchangeRepository
     */
    private $exchangeRepository;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var MessageQueueConfig
     */
    private $messageQueueConfig;

    /**
     * Initialize dependencies.
     *
     * @param ExchangeRepository $exchangeRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageQueueConfig $messageQueueConfig
     * @internal param ExchangeInterface $exchange
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        MessageQueueConfig $messageQueueConfig
    ) {
        $this->exchangeRepository = $exchangeRepository;
        $this->envelopeFactory = $envelopeFactory;
        $this->messageQueueConfig = $messageQueueConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        $envelope = $this->envelopeFactory->create(['body' => $data]);
        $connectionName = $this->messageQueueConfig->getConnectionByTopic($topicName);
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        $exchange->enqueue($topicName, $envelope);
    }
}
