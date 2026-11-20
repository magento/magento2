<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Bulk;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Bulk\ExchangeFactoryInterface;
use Magento\Framework\MessageQueue\Bulk\Queue\QueueInterface;
use Magento\Framework\MessageQueue\QueueInterface as BaseQueueInterface;
use Magento\Framework\Stomp\Config;
use Magento\Framework\Stomp\StompClientFactory;
use Stomp\Exception\StompException;

/**
 * @api
 * @deprecated
 * @see \Magento\Framework\Stomp\Bulk\Exchange
 */
class Queue implements QueueInterface
{

    /**
     * @var Config
     */
    private $stompConfig;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var StompClientFactory
     */
    private StompClientFactory $stompClientFactory;

    /**
     * @var CommunicationConfigInterface
     */
    private $communicationConfig;

    /**
     * @var ExchangeFactoryInterface
     */
    private $exchangeFactory;

    /**
     * Initialize dependencies.
     *
     * @param Config $stompConfig
     * @param string $queueName
     * @param StompClientFactory $stompClientFactory
     * @param CommunicationConfigInterface $communicationConfig
     * @param ExchangeFactoryInterface|null $exchangeFactory
     */
    public function __construct(
        Config             $stompConfig,
        string             $queueName,
        StompClientFactory $stompClientFactory,
        CommunicationConfigInterface $communicationConfig,
        ?ExchangeFactoryInterface $exchangeFactory = null,
    ) {
        $this->stompConfig = $stompConfig;
        $this->queueName = $queueName;
        $this->communicationConfig = $communicationConfig;
        $this->stompClientFactory = $stompClientFactory;
        $this->exchangeFactory = $exchangeFactory ?? ObjectManager::getInstance()
            ->get(ExchangeFactoryInterface::class);
    }

    /**
     * Push a message to queue
     *
     * @param BaseQueueInterface $queue
     * @param string $topic
     * @param array $envelopes
     * @return array|null
     * @throws LocalizedException
     * @throws StompException
     * @inheritdoc
     */
    #[\Deprecated('Bulk queue is deprecated in favor of bulk exchange as it duplicates the existing wrappers.')]
    public function push(BaseQueueInterface $queue, string $topic, array $envelopes): ?array
    {
        $exchange = $this->exchangeFactory->create($queue->getConnectionName());
        return $exchange->enqueue($topic, $envelopes);
    }

    /**
     * Get connection name
     *
     * @return string
     */
    #[\Deprecated('Connection name is just a config alias. It should not be used outside of loading config data.')]
    public function getConnectionName(): string
    {
        return $this->stompConfig->getConnectionName();
    }
}
