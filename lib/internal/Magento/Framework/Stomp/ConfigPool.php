<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

/**
 * STOMP connections pool.
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
    public function get($connectionName): Config
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
            $connection = $config->getConnection();
            $connection?->disconnect();
        }
    }
}
