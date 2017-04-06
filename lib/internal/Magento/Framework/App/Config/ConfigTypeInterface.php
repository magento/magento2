<?php
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Interface ConfigTypeInterface
 */
interface ConfigTypeInterface
{
    /**
     * Retrieve configuration data.
     *
     * Returns full configuration array in case $path is empty.
     * In case $path is not empty return value can be either array or scalar
     *
     * @param string $path
     * @return array|int|string|boolean
     */
    public function get($path = '');

    /**
     * @return void
     */
    public function clean();
}
