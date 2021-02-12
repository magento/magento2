<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

/**
 * Module statuses manager
 */
namespace Magento\Framework\Module;

/**
 * Module status manager
 *
 * Usage:
 * ```php
 *  $manager->isEnabled('Vendor_Module');
 * ```
 */
class Manager
{
    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var array
     * @deprecated 101.0.0
     */
    private $outputConfigPaths;

    /**
     * @param ModuleListInterface $moduleList
     * @param array $outputConfigPaths
     */
    public function __construct(
        ModuleListInterface $moduleList,
        array $outputConfigPaths = []
    ) {
        $this->moduleList = $moduleList;
        $this->outputConfigPaths = $outputConfigPaths;
    }

    /**
     * Whether a module is enabled in the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    public function isEnabled($moduleName)
    {
        return $this->moduleList->has($moduleName);
    }

    /**
     * Whether a module output is permitted by the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     * @deprecated 101.0.0 Magento does not support disabling/enabling modules output from the Admin Panel since 2.2.0
     * version. Magento does not support disabling/enabling modules output from the configuration files since 2.5.0.
     * Module design should explicitly state dependencies to avoid requiring output disabling.
     * @see \Magento\Framework\Module\Manager::isEnabled()
     */
    public function isOutputEnabled($moduleName)
    {
        return $this->isEnabled($moduleName);
    }
}
