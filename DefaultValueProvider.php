<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Message Queue default config value provider.
 */
class DefaultValueProvider
{
    /**
     * Default connection name.
     *
     * @var string
     */
    private $connection;

    /**
     * Default exchange name.
     *
     * @var string
     */
    private $exchange;

    /**
     * Initialize dependencies.
     *
     * @param string $connection
     * @param string $exchange
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
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get default exchange name.
     *
     * @return string
     */
    public function getExchange()
    {
        return $this->exchange;
    }
}
