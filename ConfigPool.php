<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * AMQP connections pool.
 * @since 2.2.0
 */
class ConfigPool
{
    /**
     * @var ConfigFactory
     * @since 2.2.0
     */
    private $configFactory;

    /**
     * @var Config[]
     * @since 2.2.0
     */
    private $pool = [];

    /**
     * Initialize dependencies.
     *
     * @param ConfigFactory $configFactory
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function get($connectionName)
    {
        if (!isset($this->pool[$connectionName])) {
            $this->pool[$connectionName] = $this->configFactory->create(['connectionName' => $connectionName]);
        }
        return $this->pool[$connectionName];
    }
}
