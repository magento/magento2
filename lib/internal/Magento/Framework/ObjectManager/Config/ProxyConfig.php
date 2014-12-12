<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\ObjectManager\Config;

use Magento\Framework\ObjectManager\ConfigCacheInterface;
use Magento\Framework\ObjectManager\RelationsInterface;

class ProxyConfig implements \Magento\Framework\ObjectManager\ConfigInterface
{
    /**
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     */
    protected $subjectConfig;

    /**
     * @param \Magento\Framework\ObjectManager\ConfigInterface $config
     */
    public function __construct(\Magento\Framework\ObjectManager\ConfigInterface $config)
    {
        $this->subjectConfig = $config;
    }

    /**
     * Set class relations
     *
     * @param RelationsInterface $relations
     *
     * @return void
     */
    public function setRelations(RelationsInterface $relations)
    {
        $this->subjectConfig->setRelations($relations);
    }

    /**
     * Set configuration cache instance
     *
     * @param ConfigCacheInterface $cache
     *
     * @return void
     */
    public function setCache(ConfigCacheInterface $cache)
    {
        $this->subjectConfig->setCache($cache);
    }

    /**
     * Retrieve list of arguments per type
     *
     * @param string $type
     *
     * @return array
     */
    public function getArguments($type)
    {
        return $this->subjectConfig->getArguments($type);
    }

    /**
     * Check whether type is shared
     *
     * @param string $type
     *
     * @return bool
     */
    public function isShared($type)
    {
        return $this->subjectConfig->isShared($type);
    }

    /**
     * Retrieve instance type
     *
     * @param string $instanceName
     *
     * @return mixed
     */
    public function getInstanceType($instanceName)
    {
        return $this->subjectConfig->getInstanceType($instanceName);
    }

    /**
     * Retrieve preference for type
     *
     * @param string $type
     *
     * @return string
     * @throws \LogicException
     */
    public function getPreference($type)
    {
        return $this->subjectConfig->getPreference($type);
    }

    /**
     * Extend configuration
     *
     * @param array $configuration
     *
     * @return void
     */
    public function extend(array $configuration)
    {
        $this->subjectConfig->extend($configuration);
    }

    /**
     * Returns list of virtual types
     *
     * @return array
     */
    public function getVirtualTypes()
    {
        return $this->subjectConfig->getVirtualTypes();
    }
}
