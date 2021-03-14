<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * Resource Model Interface
 *
 * @deprecated 103.0.2 Declarative schema and data patches replace old functionality and setup_module table
 * So all resources related to this table, will be deprecated since 2.3.0
 */
interface ResourceInterface
{
    /**
     * Get Module version from DB
     *
     * @param string $moduleName
     * @return false|string
     */
    public function getDbVersion($moduleName);

    /**
     * Get resource data version
     *
     * @param string $moduleName
     * @return string|false
     */
    public function getDataVersion($moduleName);

    /**
     * Set Module version
     *
     * @param string $moduleName
     * @param string $version
     * @return int
     */
    public function setDbVersion($moduleName, $version);

    /**
     * Set resource data version
     *
     * @param string $moduleName
     * @param string $version
     * @return void
     */
    public function setDataVersion($moduleName, $version);
}
