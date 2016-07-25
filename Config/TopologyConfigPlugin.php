<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;

/**
 * Plugin which provides access to topology declared in queue config using topology config interface.
 *
 * @deprecated
 */
class TopologyConfigPlugin
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
     * Read values from queue config and make them available via topology config.
     *
     * @param \Magento\Framework\MessageQueue\Topology\Config\Data $subject
     * @param \Closure $proceed
     * @param string|null $path
     * @param mixed|null $default
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGet(
        \Magento\Framework\MessageQueue\Topology\Config\Data $subject,
        \Closure $proceed,
        $path = null,
        $default = null
    ) {
        $topologyConfigData = $proceed($path, $default);
        if ($path !== null || $default !== null) {
            return $topologyConfigData;
        }
        $topologyConfigDataFromQueueConfig = $this->getTopologyConfigDataFromQueueConfig();
        foreach ($topologyConfigDataFromQueueConfig as $exchangeName => $exchangeConfig) {
            if (isset($topologyConfigData[$exchangeName])) {
                $topologyConfigData[$exchangeName]['bindings'] = array_merge(
                    $exchangeConfig['bindings'],
                    $topologyConfigData[$exchangeName]['bindings']
                );
            } else {
                $topologyConfigData[$exchangeName] = $exchangeConfig;
            }
        }
        return $topologyConfigData;
    }

    /**
     * Get data from queue config in format compatible with topology config data internal structure.
     *
     * @return array
     */
    private function getTopologyConfigDataFromQueueConfig()
    {
        $result = [];
        foreach ($this->queueConfig->getBinds() as $queueConfigBinding) {
            $topic = $queueConfigBinding['topic'];
            $destinationType = 'queue';
            $destination = $queueConfigBinding['queue'];
            $bindingId = $destinationType . '--' . $destination . '--' . $topic;
            $bindingData = [
                'id' => $bindingId,
                'destinationType' => $destinationType,
                'destination' => $destination,
                'disabled' => false,
                'topic' => $topic,
                'arguments' => []
            ];

            $exchangeName = $queueConfigBinding['exchange'];
            if (isset($result[$exchangeName])) {
                $result[$exchangeName]['bindings'][$bindingId] = $bindingData;
            } else {
                $result[$exchangeName] = [
                    'name' => $exchangeName,
                    'type' => 'topic',
                    'connection' => $this->queueConfig->getConnectionByTopic($topic),
                    'durable' => true,
                    'autoDelete' => false,
                    'internal' => false,
                    'bindings' => [$bindingId => $bindingData],
                    'arguments' => [],
                ];
            }
        }
        return $result;
    }
}
