<?php

namespace Magento\Framework\Module;

/**
 * Module status manager
 *
 * Usage:
 * ```php
 *  $manager->isEnabled('Vendor_Module');
 * ```
 *
 * @api
 */
interface ModuleManagerInterface
{
    /**
     * Retrieve whether or not a module is enabled by configuration
     *
     * @param string $moduleName Fully-qualified module name, e.g. Magento_Config
     * @return boolean Whether or not the module is enabled in the configuration
     */
    public function isEnabled(string $moduleName): bool;
}
