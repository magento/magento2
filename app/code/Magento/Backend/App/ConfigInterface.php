<?php
/**
 * Default application path for backend area
 *
 * Copyright © 2016 Magento. All rights reserved.
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
     * Path should looks like keys imploded by "/". For example scopes/stores/admin
     *
     * @param string $path
     * @return mixed
     * @api
     */
    public function getValue($path);

    /**
     * Retrieve config flag
     *
     * Path should looks like keys imploded by "/". For example scopes/stores/admin
     *
     * @param string $path
     * @return bool
     * @api
     */
    public function isSetFlag($path);
}
