<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Consumer;

use Magento\Framework\MessageQueue\ConfigInterface;
use Magento\Framework\MessageQueue\Consumer\Config\CompositeReader as ConsumerConfigCompositeReader;

/**
 * Plugin which provides access to consumers declared in queue config using consumer config interface.
 *
 * @deprecated 
 */
class ConfigReaderPlugin
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Read values from queue config and make them available via consumer config.
     * 
     * @param ConsumerConfigCompositeReader $subject
     * @param array $result
     * @param string|null $scope
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(ConsumerConfigCompositeReader $subject, $result, $scope = null)
    {
        return array_merge($this->getConsumerConfigDataFromQueueConfig(), $result);
    }

    /**
     * Get data from queue config in format compatible with consumer config data internal structure.
     *
     * @return array
     */
    private function getConsumerConfigDataFromQueueConfig()
    {
        $result = [];

        foreach ($this->config->getConsumers() as $consumerData) {
            $consumerName = $consumerData['name'];
            $handlers = [];

            foreach ($consumerData['handlers'] as $topicHandlers) {
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
