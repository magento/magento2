<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Publisher;

use Magento\Framework\MessageQueue\ConfigInterface;
use Magento\Framework\MessageQueue\Publisher\Config\CompositeReader as PublisherConfigCompositeReader;

/**
 * Plugin which provides access to publishers declared in queue config using publisher config interface.
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
     * Read values from queue config and make them available via publisher config
     *
     * @param PublisherConfigCompositeReader $subject
     * @param array $result
     * @param string|null $scope
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(PublisherConfigCompositeReader $subject, $result, $scope = null)
    {
        return array_merge($this->getPublisherConfigDataFromQueueConfig(), $result);
    }

    /**
     * Get data from queue config in format compatible with publisher config data internal structure
     *
     * @return array
     */
    private function getPublisherConfigDataFromQueueConfig()
    {
        $result = [];

        foreach ($this->config->getBinds() as $bindingConfig) {
            $topic = $bindingConfig['topic'];
            $result[$topic] = [
                'topic' => $topic,
                'connection' => [
                    'name' => $this->config->getConnectionByTopic($topic),
                    'exchange' => $bindingConfig['exchange'],
                    'disabled' => false
                ],
                'disabled' => false
            ];
        }

        return $result;
    }
}
