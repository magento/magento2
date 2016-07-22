<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;

/**
 * Plugin which provides access to publishers declared in queue config using publisher config interface.
 *
 * @deprecated
 */
class PublisherConfigPlugin
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
     * @param \Magento\Framework\MessageQueue\Publisher\Config\Data $subject
     * @param \Closure $proceed
     * @param string|null $path
     * @param mixed|null $default
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGet(
        \Magento\Framework\MessageQueue\Publisher\Config\Data $subject,
        \Closure $proceed,
        $path = null,
        $default = null
    ) {
        $publisherConfigData = $proceed($path, $default);
        if ($path !== null || $default !== null) {
            return $publisherConfigData;
        }
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
