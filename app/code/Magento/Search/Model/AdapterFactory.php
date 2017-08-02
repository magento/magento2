<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

/**
 * @api
 * @since 2.0.0
 */
class AdapterFactory
{
    /**
     * Scope configuration
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Config path
     *
     * @var string
     * @since 2.0.0
     */
    protected $path;

    /**
     * Config Scope
     * @since 2.0.0
     */
    protected $scope;

    /**
     * Pool of existing adapters
     *
     * @var array
     * @since 2.0.0
     */
    private $adapterPool;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $adapters
     * @param string $path
     * @param string $scopeType
     * @since 2.0.0
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
     * @since 2.0.0
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
