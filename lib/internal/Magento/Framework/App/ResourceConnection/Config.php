<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Resource configuration, uses application configuration to retrieve resource connection information
 * @since 2.0.0
 */
class Config extends \Magento\Framework\Config\Data\Scoped implements ConfigInterface
{
    /**
     * List of connection names per resource
     *
     * @var array
     * @since 2.0.0
     */
    protected $_connectionNames = [];

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     * @since 2.2.0
     */
    private $deploymentConfig;

    /**
     * @var bool
     * @since 2.2.0
     */
    private $initialized = false;

    /**
     * Constructor
     *
     * @param Config\Reader $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function __construct(
        Config\Reader $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        $cacheId = 'resourcesCache',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $configScope, $cache, $cacheId, $serializer);
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Retrieve resource connection instance name
     *
     * @param string $resourceName
     * @return string
     * @since 2.0.0
     */
    public function getConnectionName($resourceName)
    {
        $this->initConnections();
        $connectionName = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
 
        if (!isset($this->_connectionNames[$resourceName])) {
            $resourcesConfig = $this->get();
            $pointerResourceName = $resourceName;
            while (true) {
                if (isset($resourcesConfig[$pointerResourceName]['connection'])) {
                    $connectionName = $resourcesConfig[$pointerResourceName]['connection'];
                    $this->_connectionNames[$resourceName] = $connectionName;
                    break;
                } elseif (isset($this->_connectionNames[$pointerResourceName])) {
                    $this->_connectionNames[$resourceName] = $this->_connectionNames[$pointerResourceName];
                    $connectionName = $this->_connectionNames[$resourceName];
                    break;
                } elseif (isset($resourcesConfig[$pointerResourceName]['extends'])) {
                    $pointerResourceName = $resourcesConfig[$pointerResourceName]['extends'];
                } else {
                    break;
                }
            }
        } else {
            $connectionName = $this->_connectionNames[$resourceName];
        }

        return $connectionName;
    }

    /**
     * Initialise connections
     *
     * @return void
     * @since 2.2.0
     */
    private function initConnections()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            $resource = $this->deploymentConfig->getConfigData(ConfigOptionsListConstants::KEY_RESOURCE) ?: [];
            foreach ($resource as $resourceName => $resourceData) {
                if (!isset($resourceData['connection'])) {
                    throw new \InvalidArgumentException('Invalid initial resource configuration');
                }
                $this->_connectionNames[$resourceName] = $resourceData['connection'];
            }
        }
    }
}
