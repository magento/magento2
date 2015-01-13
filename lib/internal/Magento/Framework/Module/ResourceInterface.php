<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * Resource Model Interface
 */
interface ResourceInterface
{
    /**
     * Get Module version from DB
     *
     * @param string $resName
     * @return false|string
     */
    public function getDbVersion($resName);

    /**
     * Get resource data version
     *
     * @param string $resName
     * @return string|false
     */
    public function getDataVersion($resName);

    /**
     * Set Module version
     *
     * @param string $resName
     * @param string $version
     * @return int
     */
    public function setDbVersion($resName, $version);

    /**
     * Set resource data version
     *
     * @param string $resName
     * @param string $version
     * @return void
     */
    public function setDataVersion($resName, $version);
}
