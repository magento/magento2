<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Topology;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;

/**
 * Plugin which provides access to topology declared in queue config using topology config interface.
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
     * Read values from queue config and make them available via topology config.
     *
     * @param \Magento\Framework\MessageQueue\Topology\Config\CompositeReader $subject
     * @param array $result
     * @param string|null $scope
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(
        \Magento\Framework\MessageQueue\Topology\Config\CompositeReader $subject,
        array $result,
        $scope = null
    ) {
        $topologyConfigDataFromQueueConfig = $this->getTopologyConfigDataFromQueueConfig();
        foreach ($topologyConfigDataFromQueueConfig as $exchangeKey => $exchangeConfig) {
            if (isset($result[$exchangeKey])) {
                $result[$exchangeKey]['bindings'] = array_merge(
                    $exchangeConfig['bindings'],
                    $result[$exchangeKey]['bindings']
                );
            } else {
                $result[$exchangeKey] = $exchangeConfig;
            }
        }
        return $result;
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
            $connection = $this->queueConfig->getConnectionByTopic($topic);
            if (isset($result[$exchangeName . '--' . $connection])) {
                $result[$exchangeName . '--' . $connection]['bindings'][$bindingId] = $bindingData;
            } else {
                $result[$exchangeName . '--' . $connection] = [
                    'name' => $exchangeName,
                    'type' => 'topic',
                    'connection' => $connection,
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
