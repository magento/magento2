<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use InvalidArgumentException;
use LogicException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\AdapterInterface;
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
     * @var ScopeConfigInterface
     * @deprecated 101.0.0 since it is not used anymore
     */
    protected $scopeConfig;

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
     * @param ObjectManagerInterface $objectManager Object Manager instance
     * @param array $adapters
     * @param EngineResolverInterface $engineResolver
     */
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager,
        array $adapters,
        private readonly EngineResolverInterface $engineResolver
    ) {
        $this->adapterPool = $adapters;
    }

    /**
     * Create Adapter instance
     *
     * @param array $data
     * @return AdapterInterface
     */
    public function create(array $data = [])
    {
        $currentAdapter = $this->engineResolver->getCurrentSearchEngine();
        if (!isset($this->adapterPool[$currentAdapter])) {
            throw new LogicException(
                'There is no such adapter: ' . $currentAdapter
            );
        }
        $adapterClass = $this->adapterPool[$currentAdapter];
        $adapter = $this->objectManager->create($adapterClass, $data);
        if (!($adapter instanceof AdapterInterface)) {
            throw new InvalidArgumentException(
                'Adapter must implement \Magento\Framework\Search\AdapterInterface'
            );
        }
        return $adapter;
    }
}
