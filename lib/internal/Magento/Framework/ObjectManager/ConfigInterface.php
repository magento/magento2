<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

/**
 * Interface \Magento\Framework\ObjectManager\ConfigInterface
 *
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Set class relations
     *
     * @param RelationsInterface $relations
     *
     * @return void
     * @since 2.0.0
     */
    public function setRelations(RelationsInterface $relations);

    /**
     * Set configuration cache instance
     *
     * @param ConfigCacheInterface $cache
     *
     * @return void
     * @since 2.0.0
     */
    public function setCache(ConfigCacheInterface $cache);

    /**
     * Retrieve list of arguments per type
     *
     * @param string $type
     * @return array|null
     * @since 2.0.0
     */
    public function getArguments($type);

    /**
     * Check whether type is shared
     *
     * @param string $type
     * @return bool
     * @since 2.0.0
     */
    public function isShared($type);

    /**
     * Retrieve instance type
     *
     * @param string $instanceName
     * @return mixed
     * @since 2.0.0
     */
    public function getInstanceType($instanceName);

    /**
     * Retrieve preference for type
     *
     * @param string $type
     * @return string
     * @throws \LogicException
     * @since 2.0.0
     */
    public function getPreference($type);

    /**
     * Returns list of virtual types
     *
     * @return array
     * @since 2.0.0
     */
    public function getVirtualTypes();

    /**
     * Extend configuration
     *
     * @param array $configuration
     * @return void
     * @since 2.0.0
     */
    public function extend(array $configuration);

    /**
     * Returns list on preferences
     *
     * @return array
     * @since 2.0.0
     */
    public function getPreferences();
}
