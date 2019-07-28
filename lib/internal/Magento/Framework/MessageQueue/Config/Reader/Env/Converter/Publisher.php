<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Config\Reader\Env\Converter;

/**
 * Converts publisher related data from env.php to MessageQueue config array
 */
class Publisher implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Mapping between connection name and default exchange value
     * @var array
     */
    private $connectionToExchangeMap;

    /**
     * @param array $connectionToExchangeMap
     */
    public function __construct(
        $connectionToExchangeMap = []
    ) {
        $this->connectionToExchangeMap = $connectionToExchangeMap;
    }

    /**
     * {@inheritDoc}
     */
    public function convert($source)
    {
        $publishersConfig = isset($source[\Magento\Framework\MessageQueue\Config\Reader\Env::ENV_PUBLISHERS])
            ? $source[\Magento\Framework\MessageQueue\Config\Reader\Env::ENV_PUBLISHERS]
            : [];
        $connections = [];
        if (!empty($publishersConfig)) {
            foreach ($publishersConfig as $configuration) {
                if (isset($configuration['connections'])) {
                    $publisherData = [];
                    foreach ($configuration['connections'] as $connectionName => $config) {
                        if (isset($this->connectionToExchangeMap[$connectionName])) {
                            $publisherName = $connectionName . '-' . $this->connectionToExchangeMap[$connectionName];
                            $config['connection'] = $config['name'];
                            $config['name'] = $publisherName;
                            $publisherData[$publisherName] = $config;
                            $connections = array_replace_recursive($connections, $publisherData);
                        }
                    }
                }
            }
            $source[\Magento\Framework\MessageQueue\Config\Reader\Env::ENV_PUBLISHERS] = $connections;
        }
        return $source;
    }
}
