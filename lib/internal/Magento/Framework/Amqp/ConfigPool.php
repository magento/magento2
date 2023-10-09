<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * AMQP connections pool.
 */
class ConfigPool
{
    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var Config[]
     */
    private $pool = [];

    /**
     * Initialize dependencies.
     *
     * @param ConfigFactory $configFactory
     */
    public function __construct(ConfigFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    /**
     * Get connection by name.
     *
     * @param string $connectionName
     * @return Config
     */
    public function get($connectionName)
    {
        if (!isset($this->pool[$connectionName])) {
            $this->pool[$connectionName] = $this->configFactory->create(['connectionName' => $connectionName]);
        }
        return $this->pool[$connectionName];
    }

    /**
     * Close all opened connections.
     *
     * @return void
     */
    public function closeConnections(): void
    {
        foreach ($this->pool as $config) {
            $connection = $config->getChannel()->getConnection();
            $config->getChannel()->close();
            $connection?->close();
        }
    }
}
