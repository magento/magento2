<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection;

use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Resource configuration, uses application configuration to retrieve resource connection information
 */
class Config extends \Magento\Framework\Config\Data\Scoped implements ConfigInterface
{
    /**
     * List of connection names per resource
     *
     * @var array
     */
    protected $_connectionNames = [];

    /**
     * @param Config\Reader $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param string $cacheId
     * @throws \InvalidArgumentException
     */
    public function __construct(
        Config\Reader $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        $cacheId = 'resourcesCache'
    ) {
        parent::__construct($reader, $configScope, $cache, $cacheId);

        $resource = $deploymentConfig->getConfigData(ConfigOptionsListConstants::KEY_RESOURCE);
        foreach ($resource as $resourceName => $resourceData) {
            if (!isset($resourceData['connection'])) {
                throw new \InvalidArgumentException('Invalid initial resource configuration');
            }
            $this->_connectionNames[$resourceName] = $resourceData['connection'];
        }
    }

    /**
     * Retrieve resource connection instance name
     *
     * @param string $resourceName
     * @return string
     */
    public function getConnectionName($resourceName)
    {
        $connectionName = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;

        $resourceName = preg_replace("/_setup$/", '', $resourceName);

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
}
