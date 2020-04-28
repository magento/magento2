<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

class ExchangeRepository
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string[] $exchanges
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $exchanges = [])
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $connectionName
     * @return ExchangeInterface
     * @throws \LogicException
     */
    public function getByConnectionName($connectionName)
    {
        if (!isset($this->exchangePool[$connectionName])) {
            $exchange = $this->getExchangeFactory()->create($connectionName);
            $this->exchangePool[$connectionName] = $exchange;
        }
        return $this->exchangePool[$connectionName];
    }

    /**
     * Get exchange factory.
     *
     * @return ExchangeFactoryInterface
     * @deprecated 102.0.5
     */
    private function getExchangeFactory()
    {
        if ($this->exchangeFactory === null) {
            $this->exchangeFactory = $this->objectManager->get(ExchangeFactoryInterface::class);
        }
        return $this->exchangeFactory;
    }
}
