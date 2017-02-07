<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class ClientResolver
{
    /**
     * Scope configuration
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
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
     * Config path
     *
     * @var string
     */
    protected $path;

    /**
     * Config Scope
     */
    protected $scope;


    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig,
     * @param array $clientFactories
     * @param array $clientOptions
     * @param string $path
     * @param string scope
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        array $clientFactories,
        array $clientOptions,
        $path,
        $scopeType
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->clientFactoryPool = $clientFactories;
        $this->clientOptionsPool = $clientOptions;
        $this->path = $path;
        $this->scope = $scopeType;
    }

    /**
     * Returns configured search engine
     *
     * @return string
     */
    public function getCurrentEngine()
    {
        return $this->scopeConfig->getValue($this->path, $this->scope);
    }

    /**
     * Create client instance
     *
     * @param string $engine
     * @param array $data
     * @return ClientInterface
     */
    public function create($engine = '', array $data = [])
    {
        $engine = $engine ?: $this->getCurrentEngine();

        if (!isset($this->clientFactoryPool[$engine])) {
            throw new \LogicException(
                'There is no such client factory: ' . $engine
            );
        }
        $factoryClass = $this->clientFactoryPool[$engine];
        $factory = $this->objectManager->create($factoryClass);
        if (!($factory instanceof ClientFactoryInterface)) {
            throw new \InvalidArgumentException(
                'Client factory must implement \Magento\AdvancedSearch\Model\Client\ClientFactoryInterface'
            );
        }

        $optionsClass = $this->clientOptionsPool[$engine];
        $clientOptions = $this->objectManager->create($optionsClass);
        if (!($clientOptions instanceof ClientOptionsInterface)) {
            throw new \InvalidArgumentException(
                'Client options must implement \Magento\AdvancedSearch\Model\Client\ClientInterface'
            );
        }

        $client = $factory->create($clientOptions->prepareClientOptions($data));

        return $client;
    }
}
