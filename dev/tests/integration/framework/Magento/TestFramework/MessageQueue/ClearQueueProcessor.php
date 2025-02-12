<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\MessageQueue\ConsumerFactory;
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
     * @var ConsumerFactory
     */
    private $consumerFactory;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * ClearQueueProcessor constructor.
     *
     * @param ConsumerConfig $consumerConfig
     * @param ConsumerFactory $consumerFactory
     * @param QueueRepository $queueRepository
     */
    public function __construct(
        ConsumerConfig $consumerConfig,
        ConsumerFactory $consumerFactory,
        QueueRepository $queueRepository
    ) {
        $this->consumerConfig = $consumerConfig;
        $this->consumerFactory = $consumerFactory;
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
        while ($message = $queue->dequeue()) {
            $queue->acknowledge($message);
        }
    }
}
