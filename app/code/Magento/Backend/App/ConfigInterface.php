<?php
/**
 * Default application path for backend area
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App;

/**
 * Backend config accessor
 */
interface ConfigInterface
{
    /**
     * Retrieve config value by path
     *
     * @param string $path
     * @return mixed
     */
    public function getValue($path);

    /**
     * Set config value
     *
     * @param string $path
     * @param mixed $value
     * @return void
     */
    public function setValue($path, $value);

    /**
     * Retrieve config flag
     *
     * @param string $path
     * @return bool
     */
    public function isSetFlag($path);
}
