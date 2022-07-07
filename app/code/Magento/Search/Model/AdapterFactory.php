<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\Search\EngineResolverInterface;

/**
 * @api
 * @since 100.0.2
 */
class AdapterFactory
{
    /**
     * Scope configuration
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @deprecated 101.0.0 since it is not used anymore
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
     * @deprecated 101.0.0 since it is not used anymore
     */
    protected $path;

    /**
     * Config Scope
     * @deprecated 101.0.0 since it is not used anymore
     */
    protected $scope;

    /**
     * Pool of existing adapters
     *
     * @var array
     */
    private $adapterPool;

    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $adapters
     * @param EngineResolverInterface $engineResolver
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $adapters,
        EngineResolverInterface $engineResolver
    ) {
        $this->objectManager = $objectManager;
        $this->adapterPool = $adapters;
        $this->engineResolver = $engineResolver;
    }

    /**
     * Create Adapter instance
     *
     * @param array $data
     * @return \Magento\Framework\Search\AdapterInterface
     */
    public function create(array $data = [])
    {
        $currentAdapter = $this->engineResolver->getCurrentSearchEngine();
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
