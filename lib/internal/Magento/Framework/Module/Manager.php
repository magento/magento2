<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Module statuses manager
 */
namespace Magento\Framework\Module;

/**
 * Module status manager.
 *
 * Usage:
 * ```php
 *  $manager->isEnabled('Vendor_Module');
 * ```
 */
class Manager
{
    /**
     * @var Output\ConfigInterface
     * @deprecated 101.0.0
     */
    private $outputConfig;

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
     * @param Output\ConfigInterface $outputConfig
     * @param ModuleListInterface $moduleList
     * @param array $outputConfigPaths
     */
    public function __construct(
        Output\ConfigInterface $outputConfig,
        ModuleListInterface $moduleList,
        array $outputConfigPaths = []
    ) {
        $this->outputConfig = $outputConfig;
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
     * version. Module output can still be enabled/disabled in configuration files. However, this functionality should
     * not be used in future development. Module design should explicitly state dependencies to avoid requiring output
     * disabling. This functionality will temporarily be kept in Magento core, as there are unresolved modularity
     * issues that will be addressed in future releases.
     */
    public function isOutputEnabled($moduleName)
    {
        return $this->isEnabled($moduleName)
            && $this->_isCustomOutputConfigEnabled($moduleName)
            && !$this->outputConfig->isEnabled($moduleName);
    }

    /**
     * Whether a configuration switch for a module output permits output or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     * @deprecated 101.0.0
     */
    protected function _isCustomOutputConfigEnabled($moduleName)
    {
        if (isset($this->outputConfigPaths[$moduleName])) {
            $configPath = $this->outputConfigPaths[$moduleName];
            if (defined($configPath)) {
                $configPath = constant($configPath);
            }
            return $this->outputConfig->isSetFlag($configPath);
        }
        return true;
    }
}
