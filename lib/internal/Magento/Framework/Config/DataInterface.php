<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Config data interface.
 *
 * @api
 * @since 2.0.0
 */
interface DataInterface
{
    /**
     * Merge config data to the object
     *
     * @param array $config
     * @return void
     * @since 2.0.0
     */
    public function merge(array $config);

    /**
     * Get config value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @since 2.0.0
     */
    public function get($key, $default = null);
}
