<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * The checker of output modules.
     *
     * @var Output\ConfigInterface the config checker of output modules.
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version.
     * The property can be removed in a future major release
     */
    private $outputConfig;

    /**
     * The list of all modules.
     *
     * @var ModuleListInterface the list of all modules.
     */
    private $moduleList;

    /**
     * The list of config paths to ignore.
     *
     * @var array the list of config paths to ignore.
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version.
     * The property can be removed in a future major release
     */
    private $outputConfigPaths;

    /**
     * Constructor.
     *
     * @param Output\ConfigInterface $outputConfig the checker of output modules
     * @param ModuleListInterface $moduleList the list of all modules
     * @param array $outputConfigPaths the list of config paths to ignore
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
     * Checks whether a module is enabled in the configuration or not.
     *
     * @param string $moduleName the fully-qualified module name
     *
     * @return boolean true if module is enabled, false otherwise
     */
    public function isEnabled($moduleName)
    {
        return $this->moduleList->has($moduleName);
    }

    /**
     * Checks whether a module output is permitted by the configuration or not.
     *
     * @param string $moduleName the fully-qualified module name.
     *
     * @return boolean
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     */
    public function isOutputEnabled($moduleName)
    {
        return $this->isEnabled($moduleName);
    }

    /**
     * Checks whether a configuration switch for a module output permits output.
     *
     * @param string $moduleName Fully-qualified module name
     *
     * @return boolean
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version.
     * The method can be removed in a future major release
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isCustomOutputConfigEnabled($moduleName)
    {
        return true;
    }
}
