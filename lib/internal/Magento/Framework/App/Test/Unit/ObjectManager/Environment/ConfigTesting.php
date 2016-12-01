<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ObjectManager\Environment;

use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\ConfigCacheInterface;
use Magento\Framework\ObjectManager\RelationsInterface;

class ConfigTesting implements ConfigInterface
{

    /**
     * Set class relations
     *
     * @param RelationsInterface $relations
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setRelations(RelationsInterface $relations)
    {
        return;
    }

    /**
     * Set configuration cache instance
     *
     * @param ConfigCacheInterface $cache
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setCache(ConfigCacheInterface $cache)
    {
        return;
    }

    /**
     * Retrieve list of arguments per type
     *
     * @param string $type
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getArguments($type)
    {
        return [];
    }

    /**
     * Check whether type is shared
     *
     * @param string $type
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isShared($type)
    {
        return true;
    }

    /**
     * Retrieve instance type
     *
     * @param string $instanceName
     * @return mixed
     */
    public function getInstanceType($instanceName)
    {
        return $instanceName;
    }

    /**
     * Retrieve preference for type
     *
     * @param string $type
     * @return string
     * @throws \LogicException
     */
    public function getPreference($type)
    {
        return $type;
    }

    /**
     * Returns list of virtual types
     *
     * @return array
     */
    public function getVirtualTypes()
    {
        return [];
    }

    /**
     * Returns entire arguments keyed by type
     *
     * @return array
     */
    public function getAllArguments()
    {
        return [];
    }

    /**
     * Extend configuration
     *
     * @param array $configuration
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function extend(array $configuration)
    {
        return;
    }

    /**
     * Returns list on preferences
     *
     * @return array
     */
    public function getPreferences()
    {
        return [];
    }

    /**
     * Set Interception config
     *
     * @param \Magento\Framework\Interception\ConfigInterface $interceptionConfig
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setInterceptionConfig(\Magento\Framework\Interception\ConfigInterface $interceptionConfig)
    {
        return;
    }

    /**
     * Retrieve instance type without interception processing
     *
     * @param string $instanceName
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getOriginalInstanceType($instanceName)
    {
        return '';
    }
}
