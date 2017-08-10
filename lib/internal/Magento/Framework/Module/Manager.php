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
     */
    private $_outputConfig;

    /**
     * @var ModuleListInterface
     */
    private $_moduleList;

    /**
     * @var array
     */
    private $_outputConfigPaths;

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
        $this->_outputConfig = $outputConfig;
        $this->_moduleList = $moduleList;
        $this->_outputConfigPaths = $outputConfigPaths;
    }

    /**
     * Whether a module is enabled in the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    public function isEnabled($moduleName)
    {
        return $this->_moduleList->has($moduleName);
    }

    /**
     * Whether a module output is permitted by the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    public function isOutputEnabled($moduleName)
    {
        if (!$this->isEnabled($moduleName)) {
            return false;
        }
        if (!$this->_isCustomOutputConfigEnabled($moduleName)) {
            return false;
        }
        if ($this->_outputConfig->isEnabled($moduleName)) {
            return false;
        }
        return true;
    }

    /**
     * Whether a configuration switch for a module output permits output or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    protected function _isCustomOutputConfigEnabled($moduleName)
    {
        if (isset($this->_outputConfigPaths[$moduleName])) {
            $configPath = $this->_outputConfigPaths[$moduleName];
            if (defined($configPath)) {
                $configPath = constant($configPath);
            }
            return $this->_outputConfig->isSetFlag($configPath);
        }
        return true;
    }
}
