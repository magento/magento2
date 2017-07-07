<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\DeploymentConfig;

/**
 * In-memory readonly pool of cache front-ends with enforced access control, specific to cache types
 *
 * @api
 */
class FrontendPool
{
    /**
     * Array key for cache type
     */
    const KEY_CACHE_TYPE = 'type';

    /**
     * Array key for cache frontend
     */
    const KEY_FRONTEND_CACHE = 'frontend';

    /**
     * Config key for cache
     */
    const KEY_CACHE = 'cache';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $_frontendPool;

    /**
     * @var array
     */
    private $_typeFrontendMap;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface[]
     */
    private $_instances = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\App\Cache\Frontend\Pool $frontendPool
     * @param array $typeFrontendMap Format: array('<cache_type_id>' => '<cache_frontend_id>', ...)
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\Cache\Frontend\Pool $frontendPool,
        array $typeFrontendMap = []
    ) {
        $this->_objectManager = $objectManager;
        $this->deploymentConfig = $deploymentConfig;
        $this->_frontendPool = $frontendPool;
        $this->_typeFrontendMap = $typeFrontendMap;
    }

    /**
     * Retrieve cache frontend instance by a cache type identifier, enforcing identifier-scoped access control
     *
     * @param string $cacheType Cache type identifier
     * @return \Magento\Framework\Cache\FrontendInterface Cache frontend instance
     */
    public function get($cacheType)
    {
        if (!isset($this->_instances[$cacheType])) {
            $frontendId = $this->_getCacheFrontendId($cacheType);
            $frontendInstance = $this->_frontendPool->get($frontendId);
            /** @var $frontendInstance AccessProxy */
            $frontendInstance = $this->_objectManager->create(
                \Magento\Framework\App\Cache\Type\AccessProxy::class,
                ['frontend' => $frontendInstance, 'identifier' => $cacheType]
            );
            $this->_instances[$cacheType] = $frontendInstance;
        }
        return $this->_instances[$cacheType];
    }

    /**
     * Retrieve cache frontend identifier, associated with a cache type
     *
     * @param string $cacheType
     * @return string
     */
    protected function _getCacheFrontendId($cacheType)
    {
        $result = null;
        $cacheInfo = $this->deploymentConfig->getConfigData(self::KEY_CACHE);
        if (null !== $cacheInfo) {
            $result = isset($cacheInfo[self::KEY_CACHE_TYPE][$cacheType][self::KEY_FRONTEND_CACHE]) ?
                $cacheInfo[self::KEY_CACHE_TYPE][$cacheType][self::KEY_FRONTEND_CACHE] : null;
        }
        if (!$result) {
            if (isset($this->_typeFrontendMap[$cacheType])) {
                $result = $this->_typeFrontendMap[$cacheType];
            } else {
                $result = \Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID;
            }
        }
        return $result;
    }
}
