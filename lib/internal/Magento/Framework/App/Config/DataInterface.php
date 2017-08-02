<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Configuration data storage
 *
 * @api
 * @since 2.0.0
 */
interface DataInterface
{
    /**
     * Retrieve configuration value by path
     *
     * @param string|null $path
     * @return mixed
     * @since 2.0.0
     */
    public function getValue($path);

    /**
     * Set configuration value by path
     *
     * @param string $path
     * @param mixed $value
     * @return void
     * @since 2.0.0
     */
    public function setValue($path, $value);
}
