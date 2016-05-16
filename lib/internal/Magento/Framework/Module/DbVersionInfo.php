<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class DbVersionInfo
 *
 */
class DbVersionInfo
{
    /**#@+
     * Constants defined for keys of version info array
     */
    const KEY_MODULE = 'module';
    const KEY_TYPE = 'type';
    const KEY_CURRENT = 'current';
    const KEY_REQUIRED = 'required';
    /**#@-*/

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @param ModuleListInterface $moduleList
     * @param ResourceInterface $moduleResource
     */
    public function __construct(
        ModuleListInterface $moduleList,
        ResourceInterface $moduleResource
    ) {
        $this->moduleList = $moduleList;
        $this->moduleResource = $moduleResource;
    }

    /**
     * Check if DB schema is up to date
     *
     * @param string $moduleName
     * @return bool
     */
    public function isSchemaUpToDate($moduleName)
    {
        $dbVer = $this->moduleResource->getDbVersion($moduleName);
        return $this->isModuleVersionEqual($moduleName, $dbVer);
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function isDataUpToDate($moduleName)
    {
        $dataVer = $this->moduleResource->getDataVersion($moduleName);
        return $this->isModuleVersionEqual($moduleName, $dataVer);
    }

    /**
     * Get array of errors if DB is out of date, return [] if DB is current
     *
     * @return string[] Array of errors, each error contains module name, current version, required version,
     *                  and type (schema or data).  The array will be empty if all schema and data are current.
     */
    public function getDbVersionErrors()
    {
        $errors = [];
        foreach ($this->moduleList->getNames() as $moduleName) {
            if (!$this->isSchemaUpToDate($moduleName)) {
                $errors[] = $this->getSchemaInfo($moduleName);
            }

            if (!$this->isDataUpToDate($moduleName)) {
                $errors[] = $this->getDataInfo($moduleName);
            }
        }
        return $errors;
    }

    /**
     * Check if DB schema is up to date, version info if it is not.
     *
     * @param string $moduleName
     * @return string[] Contains current and needed version strings
     */
    private function getSchemaInfo($moduleName)
    {
        $dbVer = $this->moduleResource->getDbVersion($moduleName); // version saved in DB
        $module = $this->moduleList->getOne($moduleName);
        $configVer = $module['setup_version'];
        $dbVer = $dbVer ?: 'none';
        return [
            self::KEY_CURRENT => $dbVer,
            self::KEY_REQUIRED => $configVer,
            self::KEY_MODULE => $moduleName,
            self::KEY_TYPE => 'schema'
        ];
    }

    /**
     * Get error data for an out-of-date schema or data.
     *
     * @param string $moduleName
     * @return string[]
     */
    private function getDataInfo($moduleName)
    {
        $dataVer = $this->moduleResource->getDataVersion($moduleName);
        $module = $this->moduleList->getOne($moduleName);
        $configVer = $module['setup_version'];
        $dataVer = $dataVer ?: 'none';
        return [
            self::KEY_CURRENT => $dataVer,
            self::KEY_REQUIRED => $configVer,
            self::KEY_MODULE => $moduleName,
            self::KEY_TYPE => 'data'
        ];
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
        $module = $this->moduleList->getOne($moduleName);
        if (empty($module['setup_version'])) {
            throw new \UnexpectedValueException("Setup version for module '$moduleName' is not specified");
        }
        $configVer = $module['setup_version'];

        return ($version !== false
            && version_compare($configVer, $version) === ModuleDataSetupInterface::VERSION_COMPARE_EQUAL);
    }
}
