<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;

/**
 * Plugin which provides access to consumers declared in queue config using consumer config interface.
 *
 * @deprecated 
 */
class ConsumerConfigPlugin
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
     * Read values from queue config and make them available via consumer config.
     * 
     * @param \Magento\Framework\MessageQueue\Consumer\Config\Data $subject
     * @param \Closure $proceed
     * @param string|null $path
     * @param mixed|null $default
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGet(
        \Magento\Framework\MessageQueue\Consumer\Config\Data $subject,
        \Closure $proceed,
        $path = null,
        $default = null
    ) {
        $consumerConfigData = $proceed($path, $default);
        if ($path !== null || $default !== null) {
            return $consumerConfigData;
        }
        $consumerConfigDataFromQueueConfig = $this->getConsumerConfigDataFromQueueConfig();
        return array_merge($consumerConfigDataFromQueueConfig, $consumerConfigData);
    }

    /**
     * Get data from queue config in format compatible with consumer config data internal structure.
     *
     * @return array
     */
    private function getConsumerConfigDataFromQueueConfig()
    {
        $result = [];
        foreach ($this->queueConfig->getConsumers() as $consumerData) {
            $consumerName = $consumerData['name'];
            $handlers = [];
            foreach ($consumerData['handlers'] as $topic => $topicHandlers) {
                foreach ($topicHandlers as $handlerConfig) {
                    $handlers[] = $handlerConfig;
                }
            }
            $result[$consumerName] = [
                'name' => $consumerName,
                'queue' => $consumerData['queue'],
                'consumerInstance' => $consumerData['instance_type'],
                'handlers' => $handlers,
                'connection' => $consumerData['connection'],
                'maxMessages' => $consumerData['max_messages']
            ];
        }
        return $result;
    }
}
