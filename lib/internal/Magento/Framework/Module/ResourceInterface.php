<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * Resource Model Interface
 * @since 2.0.0
 */
interface ResourceInterface
{
    /**
     * Get Module version from DB
     *
     * @param string $moduleName
     * @return false|string
     * @since 2.0.0
     */
    public function getDbVersion($moduleName);

    /**
     * Get resource data version
     *
     * @param string $moduleName
     * @return string|false
     * @since 2.0.0
     */
    public function getDataVersion($moduleName);

    /**
     * Set Module version
     *
     * @param string $moduleName
     * @param string $version
     * @return int
     * @since 2.0.0
     */
    public function setDbVersion($moduleName, $version);

    /**
     * Set resource data version
     *
     * @param string $moduleName
     * @param string $version
     * @return void
     * @since 2.0.0
     */
    public function setDataVersion($moduleName, $version);
}
