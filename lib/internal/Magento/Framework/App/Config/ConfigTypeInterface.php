<?php
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Interface ConfigTypeInterface
 */
interface ConfigTypeInterface
{
    /**
     * Retrieve configuration raw data array.
     *
     * @param string $path
     * @return array
     */
    public function get($path = '');

    /**
     * @return void
     */
    public function clean();
}
