<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Publisher;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;

/**
 * Plugin which provides access to publishers declared in queue config using publisher config interface.
 *
 * @deprecated
 */
class ConfigReaderPlugin
{
    /**
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * Initialize dependencies.
     *
     * @param QueueConfig $queueConfig
     */
    public function __construct(QueueConfig $queueConfig)
    {
        $this->queueConfig = $queueConfig;
    }

    /**
     * Read values from queue config and make them available via publisher config.
     *
     * @param \Magento\Framework\MessageQueue\Publisher\Config\CompositeReader $subject
     * @param \Closure $proceed
     * @param string|null $scope
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRead(
        \Magento\Framework\MessageQueue\Publisher\Config\CompositeReader $subject,
        \Closure $proceed,
        $scope = null
    ) {
        $publisherConfigData = $proceed($scope);
        $publisherConfigDataFromQueueConfig = $this->getPublisherConfigDataFromQueueConfig();
        return array_merge($publisherConfigDataFromQueueConfig, $publisherConfigData);
    }

    /**
     * Get data from queue config in format compatible with publisher config data internal structure.
     *
     * @return array
     */
    private function getPublisherConfigDataFromQueueConfig()
    {
        $result = [];
        foreach ($this->queueConfig->getBinds() as $bindingConfig) {
            $topic = $bindingConfig['topic'];
            $result[$topic] = [
                'topic' => $topic,
                'connection' => [
                    'name' => $this->queueConfig->getConnectionByTopic($topic),
                    'exchange' => $bindingConfig['exchange'],
                    'disabled' => false
                ],
                'disabled' => false,
            ];
        }
        return $result;
    }
}
