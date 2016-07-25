<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Topology\Config\QueueConfigItem;
use Magento\Framework\MessageQueue\Topology\Config\Data;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;

class DataMapper
{
    /**
     * Config data.
     *
     * @var array
     */
    private $mappedData;

    /**
     * @var Data
     */
    private $configData;

    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * @var ResponseQueueNameBuilder
     */
    private $queueNameBuilder;

    /**
     * Initialize dependencies.
     *
     * @param Data $configData
     * @param CommunicationConfig $communicationConfig
     * @param ResponseQueueNameBuilder $queueNameBuilder
     */
    public function __construct(
        Data $configData,
        CommunicationConfig $communicationConfig,
        ResponseQueueNameBuilder $queueNameBuilder
    ) {
        $this->configData = $configData;
        $this->communicationConfig = $communicationConfig;
        $this->queueNameBuilder = $queueNameBuilder;
    }

    public function getMappedData()
    {
        if (null === $this->mappedData) {
            $this->mappedData = [];
            foreach ($this->configData->get() as $exchange) {
                $connection = $exchange['connection'];
                foreach ($exchange['bindings'] as $binding) {
                    if ($binding['destinationType'] === 'queue') {
                        $topicName = $binding['topic'];
                        if ($this->isSynchronousModeTopic($topicName)) {
                            $callbackQueueName = $this->queueNameBuilder->getQueueName($topicName);
                            $this->mappedData[$callbackQueueName . '-' . $connection] = [
                                'name' => $callbackQueueName,
                                'connection' => $connection,
                                'durable' => true,
                                'autoDelete' => false,
                                'arguments' => [],
                            ];
                        }
                        $queueName = $binding['destination'];
                        $this->mappedData[$queueName . '-' . $connection] = [
                            'name' => $queueName,
                            'connection' => $connection,
                            'durable' => true,
                            'autoDelete' => false,
                            'arguments' => [],
                        ];
                    }
                }
            }
        }
        return $this->mappedData;
    }

    /**
     * Check whether the topic is in synchronous mode
     *
     * @param string $topicName
     * @return bool
     * @throws LocalizedException
     */
    private function isSynchronousModeTopic($topicName)
    {
        try {
            $topic = $this->communicationConfig->getTopic($topicName);
            $isSync = (bool)$topic[CommunicationConfig::TOPIC_IS_SYNCHRONOUS];
        } catch (LocalizedException $e) {
            throw new LocalizedException(__('Error while checking if topic is synchronous'));
        }
        return $isSync;
    }
}
