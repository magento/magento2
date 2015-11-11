<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

class ClientPool
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Pool of existing client factories
     *
     * @var array
     */
    private $clientFactoryPool;

    /**
     * Pool of client option classes
     *
     * @var array
     */
    private $clientOptionsPool;


    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $clientFactories
     * @param array $clientOptions
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $clientFactories,
        array $clientOptions
    ) {
        $this->objectManager = $objectManager;
        $this->clientFactoryPool = $clientFactories;
        $this->clientOptionsPool = $clientOptions;
    }

    /**
     * Create client instance
     *
     * @param string $engine
     * @param array $data
     * @return \Magento\AdvancedSearch\Model\Client\ClientInterface
     */
    public function create($engine, array $data = [])
    {
        if (!isset($this->clientFactoryPool[$engine])) {
            throw new \LogicException(
                'There is no such client factory: ' . $engine
            );
        }
        $factoryClass = $this->clientFactoryPool[$engine];
        $factory = $this->objectManager->create($factoryClass, $data);
        if (!($factory instanceof FactoryInterface)) {
            throw new \InvalidArgumentException(
                'Client factory must implement \Magento\AdvancedSearch\Model\Client\FactoryInterface'
            );
        }

        $optionsClass = $this->clientOptionsPool[$engine];
        $clientOptions = $this->objectManager->create($optionsClass, $data);
        if (!($clientOptions instanceof ClientOptionsInterface)) {
            throw new \InvalidArgumentException(
                'Client options must implement \Magento\AdvancedSearch\Model\Client\ClientInterface'
            );
        }

        $client = $factory->create($clientOptions->prepareClientOptions($data));

        return $client;
    }
}