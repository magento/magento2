<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @var string[]
     */
    private $exchanges;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string[] $exchanges
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $exchanges)
    {
        $this->objectManager = $objectManager;
        $this->exchanges = $exchanges;
    }

    /**
     * @param string $connectionName
     * @return ExchangeInterface
     */
    public function getByConnectionName($connectionName)
    {
        if (!isset($this->exchanges[$connectionName])) {
            throw new \LogicException("Not found exchange for connection name '{$connectionName}' in config");
        }

        $exchangeClassName = $this->exchanges[$connectionName];
        $exchange = $this->objectManager->get($exchangeClassName);

        if (!$exchange instanceof ExchangeInterface) {
            $exchangeInterface = '\Magento\Framework\MessageQueue\ExchangeInterface';
            throw new \LogicException(
                "Queue '{$exchangeClassName}' for connection name '{$connectionName}' " .
                "does not implement interface '{$exchangeInterface}'"
            );
        }

        return $exchange;
    }
}
