<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Module\Updater\SetupInterface;

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
     * @var ResourceResolverInterface
     */
    private $resourceResolver;

    /**
     * @param ModuleListInterface $moduleList
     * @param ResourceInterface $moduleResource
     * @param ResourceResolverInterface $resourceResolver
     */
    public function __construct(
        ModuleListInterface $moduleList,
        ResourceInterface $moduleResource,
        ResourceResolverInterface $resourceResolver
    ) {
        $this->moduleList = $moduleList;
        $this->moduleResource = $moduleResource;
        $this->resourceResolver = $resourceResolver;
    }

    /**
     * Check if DB schema is up to date
     *
     * @param string $moduleName
     * @param string $resourceName
     * @return bool
     */
    public function isSchemaUpToDate($moduleName, $resourceName)
    {
        $dbVer = $this->moduleResource->getDbVersion($resourceName);
        return $this->isModuleVersionEqual($moduleName, $dbVer);
    }

    /**
     * @param string $moduleName
     * @param string $resourceName
     * @return bool
     */
    public function isDataUpToDate($moduleName, $resourceName)
    {
        $dataVer = $this->moduleResource->getDataVersion($resourceName);
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
            foreach ($this->resourceResolver->getResourceList($moduleName) as $resourceName) {
                if (!$this->isSchemaUpToDate($moduleName, $resourceName)) {
                    $errors[] = $this->getSchemaInfo($moduleName, $resourceName);
                }

                if (!$this->isDataUpToDate($moduleName, $resourceName)) {
                    $errors[] = $this->getDataInfo($moduleName, $resourceName);
                }
            }
        }
        return $errors;
    }

    /**
     * Check if DB schema is up to date, version info if it is not.
     *
     * @param string $moduleName
     * @param string $resourceName
     * @return string[] Contains current and needed version strings
     */
    private function getSchemaInfo($moduleName, $resourceName)
    {
        $dbVer = $this->moduleResource->getDbVersion($resourceName); // version saved in DB
        $module = $this->moduleList->getOne($moduleName);
        $configVer = $module['schema_version'];
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
     * @param string $resourceName
     * @return string[]
     */
    private function getDataInfo($moduleName, $resourceName)
    {
        $dataVer = $this->moduleResource->getDataVersion($resourceName);
        $module = $this->moduleList->getOne($moduleName);
        $configVer = $module['schema_version'];
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
        if (empty($module['schema_version'])) {
            throw new \UnexpectedValueException("Schema version for module '$moduleName' is not specified");
        }
        $configVer = $module['schema_version'];

        return ($version !== false
            && version_compare($configVer, $version) === SetupInterface::VERSION_COMPARE_EQUAL);
    }
}
