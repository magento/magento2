<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\MessageQueue\QueueRepository;

/**
 * The processor to clear message queue
 */
class ClearQueueProcessor
{
    /**
     * @var ConsumerConfig
     */
    private $consumerConfig;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @param ConsumerConfig $consumerConfig
     * @param QueueRepository $queueRepository
     */
    public function __construct(
        ConsumerConfig $consumerConfig,
        QueueRepository $queueRepository
    ) {
        $this->consumerConfig = $consumerConfig;
        $this->queueRepository = $queueRepository;
    }

    /**
     * Clear queue
     *
     * @param string $consumerName
     * @throws LocalizedException
     * return void
     */
    public function execute(string $consumerName): void
    {
        /** @var ConsumerConfigItemInterface $consumerConfig */
        $consumerConfig = $this->consumerConfig->getConsumer($consumerName);
        $queue = $this->queueRepository->get($consumerConfig->getConnection(), $consumerConfig->getQueue());
        $queue->clearQueue();
    }
}
