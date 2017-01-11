<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

class AdapterFactory
{
    /**
     * Scope configuration
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

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
     * Pool of existing adapters
     *
     * @var array
     */
    private $adapterPool;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $adapters
     * @param string $path
     * @param string $scopeType
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $adapters,
        $path,
        $scopeType
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->adapterPool = $adapters;
        $this->path = $path;
        $this->scope = $scopeType;
    }

    /**
     * Create Adapter instance
     *
     * @param array $data
     * @return \Magento\Framework\Search\AdapterInterface
     */
    public function create(array $data = [])
    {
        $currentAdapter = $this->scopeConfig->getValue($this->path, $this->scope);
        if (!isset($this->adapterPool[$currentAdapter])) {
            throw new \LogicException(
                'There is no such adapter: ' . $currentAdapter
            );
        }
        $adapterClass = $this->adapterPool[$currentAdapter];
        $adapter = $this->objectManager->create($adapterClass, $data);
        if (!($adapter instanceof \Magento\Framework\Search\AdapterInterface)) {
            throw new \InvalidArgumentException(
                'Adapter must implement \Magento\Framework\Search\AdapterInterface'
            );
        }
        return $adapter;
    }
}
