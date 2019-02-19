<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

/**
 * Interface \Magento\Framework\ObjectManager\ConfigCacheInterface
 *
 */
interface ConfigCacheInterface
{
    /**
     * Retrieve configuration from cache
     *
     * @param string $key
     * @return array
     */
    public function get($key);

    /**
     * Save config to cache
     *
     * @param array $config
     * @param string $key
     * @return void
     */
    public function save(array $config, $key);
}
