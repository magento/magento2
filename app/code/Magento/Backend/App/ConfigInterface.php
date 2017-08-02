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
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getValue($path);

    /**
     * Set config value
     *
     * @deprecated 2.2.0
     * @param string $path
     * @param mixed $value
     * @return void
     * @api
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isSetFlag($path);
}
