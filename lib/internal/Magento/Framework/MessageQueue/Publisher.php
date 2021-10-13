<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\Amqp\Config as AmqpConfig;
use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;

/**
 * A MessageQueue Publisher to handle publishing a message.
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
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * @var PublisherConfig
     */
    private $publisherConfig;

    /**
     * Help check whether Amqp is configured.
     *
     * @var AmqpConfig
     */
    private $amqpConfig;

    /**
     * Initialize dependencies.
     *
     * @param ExchangeRepository $exchangeRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageQueueConfig $messageQueueConfig
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
     * @internal param ExchangeInterface $exchange
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        MessageQueueConfig $messageQueueConfig,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator
    ) {
        $this->exchangeRepository = $exchangeRepository;
        $this->envelopeFactory = $envelopeFactory;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        $this->messageValidator->validate($topicName, $data);
        $data = $this->messageEncoder->encode($topicName, $data);
        $envelope = $this->envelopeFactory->create(
            [
                'body' => $data,
                'properties' => [
                    'delivery_mode' => 2,
                    // md5() here is not for cryptographic use.
                    // phpcs:ignore Magento2.Security.InsecureFunction
                    'message_id' => md5(uniqid($topicName))
                ]
            ]
        );
        $connectionName = $this->getPublisherConfig()->getPublisher($topicName)->getConnection()->getName();
        $connectionName = ($connectionName === 'amqp' && !$this->isAmqpConfigured()) ? 'db' : $connectionName;
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        $exchange->enqueue($topicName, $envelope);
        return null;
    }

    /**
     * Check Amqp is configured.
     *
     * @return bool
     */
    private function isAmqpConfigured()
    {
        return $this->getAmqpConfig()->getValue(AmqpConfig::HOST) ? true : false;
    }

    /**
     * Get publisher config.
     *
     * @return PublisherConfig
     *
     * @deprecated 103.0.0
     */
    private function getPublisherConfig()
    {
        if ($this->publisherConfig === null) {
            $this->publisherConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(PublisherConfig::class);
        }
        return $this->publisherConfig;
    }

    /**
     * Get Amqp config instance.
     *
     * @return AmqpConfig
     *
     * @deprecated 100.2.0 103.0.0
     */
    private function getAmqpConfig()
    {
        if ($this->amqpConfig === null) {
            $this->amqpConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(AmqpConfig::class);
        }

        return $this->amqpConfig;
    }
}
