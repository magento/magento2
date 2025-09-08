<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Stomp\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\MessageQueue\QueueRepository;

/**
 * A MessageQueue Publisher to handle publishing a message.
 */
class Publisher implements PublisherInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\QueueRepository
     */
    private QueueRepository $queueRepository;

    /**
     * @var EnvelopeFactory
     */
    private EnvelopeFactory $envelopeFactory;

    /**
     * @var MessageEncoder
     */
    private MessageEncoder $messageEncoder;

    /**
     * @var MessageValidator
     */
    private MessageValidator $messageValidator;

    /**
     * @var PublisherConfig
     */
    private PublisherConfig $publisherConfig;

    /**
     * Initialize dependencies.
     *
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
     * @param QueueRepository $queueRepository
     * @param PublisherConfig $publisherConfig
     * @internal param ExchangeInterface $exchange
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        EnvelopeFactory $envelopeFactory,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator,
        QueueRepository $queueRepository,
        PublisherConfig $publisherConfig
    ) {
        $this->envelopeFactory = $envelopeFactory;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
        $this->queueRepository = $queueRepository;
        $this->publisherConfig = $publisherConfig;
    }

    /**
     * @inheritdoc
     */
    public function publish($topicName, $data)
    {
        $this->messageValidator->validate($topicName, $data);
        $data = $this->messageEncoder->encode($topicName, $data);
        $envelope = $this->envelopeFactory->create(
            [
                'body' => $data,
                'properties' => [
                    'persistent' => 'true',
                    'topic_name' => $topicName,
                    // md5() here is not for cryptographic use.
                    // phpcs:ignore Magento2.Security.InsecureFunction
                    'message_id' => md5(gethostname() . microtime(true) . uniqid($topicName, true)),
                    'destination-type' => 'ANYCAST'
                ]
            ]
        );

        $publisher = $this->publisherConfig->getPublisher($topicName);
        $connectionName = $publisher->getConnection()->getName();
        $queue = $this->queueRepository->get($connectionName, $publisher->getQueue());

        $queue->push($envelope);
        return null;
    }
}
