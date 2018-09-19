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
 */
interface DataInterface
{
    /**
     * Retrieve configuration value by path
     *
     * @param string|null $path
     * @return mixed
     */
    public function getValue($path);

    /**
     * Set configuration value by path
     *
     * @param string $path
     * @param mixed $value
     * @return void
     */
    public function setValue($path, $value);
}
