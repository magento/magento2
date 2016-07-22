<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\MessageQueue\Consumer\Config\DataInterface;
use Magento\Framework\MessageQueue\Consumer\Config\Data as NewConfigData;
use Magento\Framework\MessageQueue\ConfigInterface as DeprecatedConfig;

/**
 * Consumer config data storage. Provides access to merged data from both new and deprecated queue configs.
 * 
 * @deprecated
 */
class CombinedData implements DataInterface
{
    /**
     * @var Data
     */
    private $newConfigData;

    /**
     * @var DeprecatedConfig
     */
    private $deprecatedConfig;

    /**
     * Initialize dependencies.
     *
     * @param NewConfigData $newConfigData
     * @param DeprecatedConfig $deprecatedConfig
     */
    public function __construct(NewConfigData $newConfigData, DeprecatedConfig $deprecatedConfig)
    {
        $this->newConfigData = $newConfigData;
        $this->deprecatedConfig = $deprecatedConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $newConfigData = $this->newConfigData->get();
        $deprecatedConfigData = $this->getDeprecatedConfigData();
        return array_merge($deprecatedConfigData, $newConfigData);
    }

    /**
     * Get data from deprecated config in format compatible with new config data internal structure.
     * 
     * @return array
     */
    private function getDeprecatedConfigData()
    {
        $result = [];
        foreach ($this->deprecatedConfig->getConsumers() as $consumerData) {
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
