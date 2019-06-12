<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Bulk;

/**
 * Used to get exchange instance from the pool.
 */
class ExchangeRepository
{
    /**
     * @var ExchangeFactoryInterface
     */
    private $exchangeFactory;

    /**
     * Pool of exchange instances.
     *
     * @var ExchangeInterface[]
     */
    private $exchangePool = [];

    /**
     * @param ExchangeFactoryInterface $exchangeFactory
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
