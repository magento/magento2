<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Bulk;

/**
 * Used to get exchange instance from the pool.
 * @since 2.2.0
 */
class ExchangeRepository
{
    /**
     * @var ExchangeFactoryInterface
     * @since 2.2.0
     */
    private $exchangeFactory;

    /**
     * Pool of exchange instances.
     *
     * @var ExchangeInterface[]
     * @since 2.2.0
     */
    private $exchangePool = [];

    /**
     * @param ExchangeFactoryInterface $exchangeFactory
     * @since 2.2.0
     */
    public function __construct(ExchangeFactoryInterface $exchangeFactory)
    {
        $this->exchangeFactory = $exchangeFactory;
    }

    /**
     * Get exchange from the pool for the specified connection type.
     *
     * @param string $connectionName
     * @return ExchangeInterface
     * @throws \LogicException
     * @since 2.2.0
     */
    public function getByConnectionName($connectionName)
    {
        if (!isset($this->exchangePool[$connectionName])) {
            $exchange = $this->exchangeFactory->create($connectionName);
            $this->exchangePool[$connectionName] = $exchange;
        }
        return $this->exchangePool[$connectionName];
    }
}
