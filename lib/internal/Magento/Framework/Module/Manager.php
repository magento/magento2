<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * Module status manager.
 *
 * Usage:
 *
 *  $manager->isEnabled('Vendor_Module')
 */
class Manager
{
    /**
     * The checker of output modules.
     *
     * @var Output\ConfigInterface the config checker of output modules.
     * @deprecated
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
     * @deprecated
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
     * @deprecated
     * @see \Magento\Framework\Module\Manager::isEnabled()
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
     * @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isCustomOutputConfigEnabled($moduleName)
    {
        return true;
    }
}
