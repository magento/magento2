<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

    /**#@-*/
    private $moduleList;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var array
     */
    private $dbVersionErrorsCache = null;

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
     * Get array of errors if DB is out of date, return [] if DB is current.
     *
     * @return string[] Array of errors, each error contains module name, current version, required version,
     *                  and type (schema or data).  The array will be empty if all schema and data are current.
     */
    public function getDbVersionErrors()
    {
        if ($this->dbVersionErrorsCache === null) {
            $this->dbVersionErrorsCache = [];
            foreach ($this->moduleList->getNames() as $moduleName) {
                if (!$this->isSchemaUpToDate($moduleName)) {
                    $this->dbVersionErrorsCache[] = $this->getSchemaInfo($moduleName);
                }
                if (!$this->isDataUpToDate($moduleName)) {
                    $this->dbVersionErrorsCache[] = $this->getDataInfo($moduleName);
                }
            }
        }

        return $this->dbVersionErrorsCache;
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
     */
    private function isModuleVersionEqual($moduleName, $version)
    {
        $module = $this->moduleList->getOne($moduleName);
        $configVer = isset($module['setup_version']) ? $module['setup_version'] : null;

        if (empty($configVer)) {
            /**
             * If setup_version was removed, this means that we want to ignore old scripts and do installation only
             * with declarative schema and data/schema patches
             */
            return true;
        }

        return version_compare($configVer, $version) === ModuleDataSetupInterface::VERSION_COMPARE_EQUAL;
    }
}
