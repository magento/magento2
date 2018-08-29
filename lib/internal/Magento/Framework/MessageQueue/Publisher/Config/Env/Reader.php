<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Publisher\Config\Env;

use Magento\Framework\MessageQueue\Config\Data as MessageQueueConfigData;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\MessageQueue\Config\Reader\Env as MessageQueueEnvReader;

/**
 * Publisher configuration reader. Reads data from env.php.
 */
class Reader implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Env
     */
    private $deploymentConfig;

    /**
     * @var MessageQueueConfigData
     */
    private $configData;

    /**
     * Mapping between default publishers name and connections
     *
     * @var array
     */
    private $publisherNameToConnectionMap;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param MessageQueueConfigData $configData
     * @param array $publisherNameToConnectionMap
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        MessageQueueConfigData $configData,
        $publisherNameToConnectionMap = []
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->configData = $configData;
        $this->publisherNameToConnectionMap = $publisherNameToConnectionMap;
    }

    /**
     * Read publisher configuration from env.php
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $configData = $this->deploymentConfig->getConfigData(MessageQueueEnvReader::ENV_QUEUE);
        if (isset($configData['config'])) {
            $configData = isset($configData['config'][MessageQueueEnvReader::ENV_PUBLISHERS])
                ? $configData['config'][MessageQueueEnvReader::ENV_PUBLISHERS]
                : [];
        } else {
            $configData = isset($configData[MessageQueueEnvReader::ENV_PUBLISHERS])
                ? $this->convertConfigData($scope)
                : [];
        }
        return $configData;
    }

    /**
     * Convert publisher related data to publisher config format
     *
     * @param string|null $scope
     * @return array
     */
    private function convertConfigData($scope)
    {
        $configData = [];
        $topicsConfig = $this->configData->get('topics');
        foreach ($topicsConfig as $topicName => $topicConfig) {
            $configData[$topicName] = [];
            if (isset($topicConfig['disabled'])) {
                $configData[$topicName]['disabled'] = $topicConfig['disabled'];
            }
            $publisherName = $this->configData->get('topics/' . $topicName . '/publisher', $scope);
            $config = $this->configData->get('publishers/' . $publisherName, $scope);
            if (!empty($config) && isset($this->publisherNameToConnectionMap[$publisherName])) {
                $connectionName = $this->publisherNameToConnectionMap[$publisherName];
                $config['name'] = $config['connection'];
                unset($config['connection']);
                $disabled = isset($config['disabled']) ? $config['disabled'] : false;
                $config['disabled'] = $disabled;
                $configData[$topicName]['connections'][$connectionName] = $config;
            }
        }
        return $configData;
    }
}
