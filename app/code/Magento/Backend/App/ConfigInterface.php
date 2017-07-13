<?php
/**
 * Default application path for backend area
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App;

/**
 * Backend config accessor
 * @api
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
     * Set config value
     *
     * @deprecated
     * @param string $path
     * @param mixed $value
     * @return void
     * @api
     */
    public function setValue($path, $value);

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
