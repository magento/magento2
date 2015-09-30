<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\EnvelopeFactory;
use Magento\Framework\Amqp\ExchangeRepository;
use Magento\Framework\Amqp\PublisherInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Amqp\Config\Data as AmqpConfig;

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
     * @var AmqpConfig
     */
    private $amqpConfig;

    /**
     * Initialize dependencies.
     *
     * @param ExchangeRepository $exchangeRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param AmqpConfig $amqpConfig
     * @internal param ExchangeInterface $exchange
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        AmqpConfig $amqpConfig
    ) {
        $this->exchangeRepository = $exchangeRepository;
        $this->envelopeFactory = $envelopeFactory;
        $this->amqpConfig = $amqpConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        $envelope = $this->envelopeFactory->create(['body' => $data]);
        $connectionName = $this->amqpConfig->getConnectionByTopic($topicName);
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        $exchange->enqueue($topicName, $envelope);
    }
}
