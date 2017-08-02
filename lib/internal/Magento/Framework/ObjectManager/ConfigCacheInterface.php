<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

/**
 * Interface \Magento\Framework\ObjectManager\ConfigCacheInterface
 *
 * @since 2.0.0
 */
interface ConfigCacheInterface
{
    /**
     * Retrieve configuration from cache
     *
     * @param string $key
     * @return array
     * @since 2.0.0
     */
    public function get($key);

    /**
     * Save config to cache
     *
     * @param array $config
     * @param string $key
     * @return void
     * @since 2.0.0
     */
    public function save(array $config, $key);
}
