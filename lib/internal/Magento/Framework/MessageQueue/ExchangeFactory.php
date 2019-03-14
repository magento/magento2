<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\ExchangeInterface
 *
 * @api
 * @since 102.0.1
 */
class ExchangeFactory implements ExchangeFactoryInterface
{
    /**
     * @var ExchangeFactoryInterface[]
     */
    private $exchangeFactories;

    /**
     * @var ConnectionTypeResolver
     */
    private $connectionTypeResolver;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 102.0.1
     */
    protected $objectManager = null;

    /**
     * Initialize dependencies.
     *
     * @param ConnectionTypeResolver $connectionTypeResolver
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ExchangeFactoryInterface[] $exchangeFactories
     */
    public function __construct(
        ConnectionTypeResolver $connectionTypeResolver,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $exchangeFactories = []
    ) {
        $this->objectManager = $objectManager;
        $this->exchangeFactories = $exchangeFactories;
        $this->connectionTypeResolver = $connectionTypeResolver;
    }

    /**
     * {@inheritdoc}
     * @since 102.0.1
     */
    public function create($connectionName, array $data = [])
    {
        $connectionType = $this->connectionTypeResolver->getConnectionType($connectionName);

        if (!isset($this->exchangeFactories[$connectionType])) {
            throw new \LogicException("Not found exchange for connection name '{$connectionName}' in config");
        }

        $factory = $this->exchangeFactories[$connectionType];
        $exchange = $factory->create($connectionName, $data);

        if (!$exchange instanceof ExchangeInterface) {
            $exchangeInterface = \Magento\Framework\MessageQueue\ExchangeInterface::class;
            throw new \LogicException(
                "Exchange for connection name '{$connectionName}' " .
                "does not implement interface '{$exchangeInterface}'"
            );
        }
        return $exchange;
    }
}
