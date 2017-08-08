<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Message Queue default config value provider.
 * @since 2.2.0
 */
class DefaultValueProvider
{
    /**
     * Default connection name.
     *
     * @var string
     * @since 2.2.0
     */
    private $connection;

    /**
     * Default exchange name.
     *
     * @var string
     * @since 2.2.0
     */
    private $exchange;

    /**
     * Initialize dependencies.
     *
     * @param string $connection
     * @param string $exchange
     * @since 2.2.0
     */
    public function __construct($connection = 'amqp', $exchange = 'magento')
    {
        $this->connection = $connection;
        $this->exchange = $exchange;
    }

    /**
     * Get default connection name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get default exchange name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getExchange()
    {
        return $this->exchange;
    }
}
