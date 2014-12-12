<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Module statuses manager
 */
namespace Magento\Framework\Module;

use Magento\Framework\Module\Updater\SetupInterface;

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
     * @var ResourceInterface
     */
    private $_moduleResource;

    /**
     * @param Output\ConfigInterface $outputConfig
     * @param ModuleListInterface $moduleList
     * @param ResourceInterface $moduleResource
     * @param array $outputConfigPaths
     */
    public function __construct(
        Output\ConfigInterface $outputConfig,
        ModuleListInterface $moduleList,
        ResourceInterface $moduleResource,
        array $outputConfigPaths = []
    ) {
        $this->_outputConfig = $outputConfig;
        $this->_moduleList = $moduleList;
        $this->_outputConfigPaths = $outputConfigPaths;
        $this->_moduleResource = $moduleResource;
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

    /**
     * Check if DB schema is up to date
     *
     * @param string $moduleName
     * @param string $resourceName
     * @return bool
     */
    public function isDbSchemaUpToDate($moduleName, $resourceName)
    {
        $dbVer = $this->_moduleResource->getDbVersion($resourceName);
        return $this->isModuleVersionEqual($moduleName, $dbVer);
    }

    /**
     * @param string $moduleName
     * @param string $resourceName
     * @return bool
     */
    public function isDbDataUpToDate($moduleName, $resourceName)
    {
        $dataVer = $this->_moduleResource->getDataVersion($resourceName);
        return $this->isModuleVersionEqual($moduleName, $dataVer);
    }

    /**
     * Check if DB data is up to date
     *
     * @param string $moduleName
     * @param string|bool $version
     * @return bool
     * @throws \UnexpectedValueException
     */
    private function isModuleVersionEqual($moduleName, $version)
    {
        $module = $this->_moduleList->getOne($moduleName);
        if (empty($module['schema_version'])) {
            throw new \UnexpectedValueException("Schema version for module '$moduleName' is not specified");
        }
        $configVer = $module['schema_version'];

        return ($version !== false && version_compare($configVer, $version) === SetupInterface::VERSION_COMPARE_EQUAL);
    }
}
