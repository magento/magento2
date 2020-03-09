<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache\Frontend;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\App\DeploymentConfig;

/**
 * In-memory readonly pool of all cache front-end instances known to the system
 */
class Pool implements \Iterator
{
    /**
     * Frontend identifier associated with the default settings
     */
    const DEFAULT_FRONTEND_ID = 'default';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var Factory
     */
    private $_factory;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface[]
     */
    private $_instances;

    /**
     * @var array
     */
    private $_frontendSettings;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param Factory $frontendFactory
     * @param array $frontendSettings Format: array('<frontend_id>' => array(<cache_settings>), ...)
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        Factory $frontendFactory,
        array $frontendSettings = []
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->_factory = $frontendFactory;
        $this->_frontendSettings = $frontendSettings + [self::DEFAULT_FRONTEND_ID => []];
    }

    /**
     * Create instances of every cache frontend known to the system.
     *
     * Method is to be used for delayed initialization of the iterator.
     *
     * @return void
     */
    protected function _initialize()
    {
        if ($this->_instances === null) {
            $this->_instances = [];
            foreach ($this->_getCacheSettings() as $frontendId => $frontendOptions) {
                $this->_instances[$frontendId] = $this->_factory->create($frontendOptions);
            }
        }
    }

    /**
     * Retrieve settings for all cache front-ends known to the system
     *
     * @return array Format: array('<frontend_id>' => array(<cache_settings>), ...)
     */
    protected function _getCacheSettings()
    {
        /*
         * Merging is intentionally implemented through array_replace_recursive() instead of array_merge(), because even
         * though some settings may become irrelevant when the cache storage type is changed, they don't do any harm
         * and can be overwritten when needed.
         * Also array_merge leads to unexpected behavior, for for example by dropping the
         * default cache_dir setting from di.xml when a cache id_prefix is configured in app/etc/env.php.
         */
        $cacheInfo = $this->deploymentConfig->getConfigData(FrontendPool::KEY_CACHE);
        if (null !== $cacheInfo) {
            return array_replace_recursive($this->_frontendSettings, $cacheInfo[FrontendPool::KEY_FRONTEND_CACHE]);
        }
        return $this->_frontendSettings;
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    public function current()
    {
        $this->_initialize();
        return current($this->_instances);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        $this->_initialize();
        return key($this->_instances);
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->_initialize();
        next($this->_instances);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->_initialize();
        reset($this->_instances);
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        $this->_initialize();
        return (bool)current($this->_instances);
    }

    /**
     * Retrieve frontend instance by its unique identifier
     *
     * @param string $identifier Cache frontend identifier
     * @return \Magento\Framework\Cache\FrontendInterface Cache frontend instance
     * @throws \InvalidArgumentException
     */
    public function get($identifier)
    {
        $this->_initialize();
        if (isset($this->_instances[$identifier])) {
            return $this->_instances[$identifier];
        }

        if (!isset($this->_instances[self::DEFAULT_FRONTEND_ID])) {
            throw new \InvalidArgumentException(
                "Cache frontend '{$identifier}' is not recognized. As well as " .
                self::DEFAULT_FRONTEND_ID .
                "cache is not configured"
            );
        }

        return $this->_instances[self::DEFAULT_FRONTEND_ID];
    }
}
