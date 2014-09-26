<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Setup\Module;

use Magento\Setup\Module\Setup\Connection\AdapterInterface;
use Magento\Setup\Module\Setup\FileResolver as SetupFileResolver;
use Magento\Setup\Module\Resource\Resource;
use Magento\Setup\Model\LoggerInterface;

class SetupModule extends Setup
{
    /**
     * Setup resource name
     * @var string
     */
    protected $resourceName;

    /**
     * Setup module configuration object
     *
     * @var array
     */
    protected $moduleConfig;

    /**
     * Resource
     *
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param AdapterInterface $connection
     * @param ModuleListInterface $moduleList
     * @param SetupFileResolver $setupFileResolver
     * @param LoggerInterface $log
     * @param string $moduleName
     * @param array $connectionConfig
     */
    public function __construct(
        AdapterInterface $connection,
        ModuleListInterface $moduleList,
        SetupFileResolver $setupFileResolver,
        LoggerInterface $log,
        $moduleName,
        array $connectionConfig = array()
    ) {
        parent::__construct($connection, $setupFileResolver, $log, $connectionConfig);
        $this->moduleConfig = $moduleList->getModule($moduleName);
        $this->resource = new Resource($this->connection);
        $this->resourceName = $this->setupFileResolver->getResourceCode($moduleName);
    }

    /**
     * Apply module recurring post schema updates
     *
     * @return $this
     * @throws \Exception
     */
    public function applyRecurringUpdates()
    {
        $moduleName = (string)$this->moduleConfig['name'];
        foreach ($this->setupFileResolver->getSqlSetupFiles($moduleName, self::TYPE_DB_RECURRING . '.php') as $file) {
            try {
                $file = $this->setupFileResolver->getAbsolutePath($file);
                $this->includeFile($file);
            } catch (\Exception $e) {
                throw new \Exception(sprintf('Error in file: "%s" - %s', $file, $e->getMessage()), 0, $e);
            }
        }
        return $this;
    }

    /**
     * Retrieve available Database install/upgrade files for current module
     *
     * @param string $actionType
     * @param string $fromVersion
     * @param string $toVersion
     * @return array
     */
    protected function getAvailableDbFiles($actionType, $fromVersion, $toVersion)
    {
        $moduleName = (string)$this->moduleConfig['name'];
        $dbFiles = array();
        $typeFiles = array();
        $regExpDb = sprintf('#%s-(.*)\.(php|sql)$#i', $actionType);
        $regExpType = sprintf('#%s-%s-(.*)\.(php|sql)$#i', 'mysql4', $actionType);
        foreach ($this->setupFileResolver->getSqlSetupFiles($moduleName, '*.{php,sql}') as $file) {
            $matches = array();
            if (preg_match($regExpDb, $file, $matches)) {
                $dbFiles[$matches[1]] = $this->setupFileResolver->getAbsolutePath($file);
            } elseif (preg_match($regExpType, $file, $matches)) {
                $typeFiles[$matches[1]] = $this->setupFileResolver->getAbsolutePath($file);
            }
        }

        if (empty($typeFiles) && empty($dbFiles)) {
            return array();
        }

        foreach ($typeFiles as $version => $file) {
            $dbFiles[$version] = $file;
        }

        return $this->prepareUpgradeFileCollection($actionType, $fromVersion, $toVersion, $dbFiles);
    }

    /**
     * Apply module resource install, upgrade and data scripts
     *
     * @return $this
     */
    public function applyUpdates()
    {
        if (!$this->resourceName) {
            return $this;
        }
        $dbVer = $this->resource->getDbVersion($this->resourceName);
        $configVer = $this->moduleConfig['schema_version'];

        // Module is installed
        if ($dbVer !== false) {
            if (version_compare($configVer, $dbVer) == self::VERSION_COMPARE_GREATER) {
                $this->applySchemaUpdates(self::TYPE_DB_UPGRADE, $dbVer, $configVer);
                $this->resource->setDbVersion($this->resourceName, $configVer);
            }
        } elseif ($configVer) {
            $oldVersion = $this->applySchemaUpdates(self::TYPE_DB_INSTALL, '', $configVer);
            $this->applySchemaUpdates(self::TYPE_DB_UPGRADE, $oldVersion, $configVer);
            $this->resource->setDbVersion($this->resourceName, $configVer);
        }
        return $this;
    }

    /**
     * Run module modification files. Return version of last applied upgrade (false if no upgrades applied)
     * @param string $actionType
     * @param string $fromVersion
     * @param string $toVersion
     * @return false|string
     * @throws \Exception
     */
    protected function applySchemaUpdates($actionType, $fromVersion, $toVersion)
    {
        $files = $this->getAvailableDbFiles($actionType, $fromVersion, $toVersion);

        if (empty($files) || !$this->getConnection()) {
            return false;
        }

        $version = false;
        foreach ($files as $file) {
            $fileName = $file['fileName'];
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
            try {
                switch ($fileType) {
                    case 'php':
                        $result = $this->includeFile($fileName);
                        break;
                    default:
                        $result = false;
                        break;
                }

                if ($result) {
                    $this->resource->setDbVersion($this->resourceName, $file['toVersion']);
                    //@todo log
                } else {
                    //@todo log "Failed resource setup: {$fileName}";
                }
            } catch (\Exception $e) {
                throw new \Exception(sprintf('Error in file: "%s" - %s', $fileName, $e->getMessage()), 0, $e);
            }
            $version = $file['toVersion'];
        }
        return $version;
    }

    /**
     * Get data files for modifications
     *
     * @param string $actionType
     * @param string $fromVersion
     * @param string $toVersion
     * @param array $arrFiles
     * @return array
     */
    protected function prepareUpgradeFileCollection($actionType, $fromVersion, $toVersion, $arrFiles)
    {
        $arrRes = [];
        switch ($actionType) {
            case self::TYPE_DB_INSTALL:
                uksort($arrFiles, 'version_compare');
                foreach ($arrFiles as $version => $file) {
                    if (version_compare($version, $toVersion) !== self::VERSION_COMPARE_GREATER) {
                        $arrRes[0] = [
                            'toVersion' => $version,
                            'fileName'  => $file
                        ];
                    }
                }
                break;

            case self::TYPE_DB_UPGRADE:
                uksort($arrFiles, 'version_compare');
                foreach ($arrFiles as $version => $file) {
                    $versionInfo = explode('-', $version);

                    // In array must be 2 elements: 0 => version from, 1 => version to
                    if (count($versionInfo) !== 2) {
                        break;
                    }
                    $infoFrom = $versionInfo[0];
                    $infoTo   = $versionInfo[1];
                    if (version_compare($infoFrom, $fromVersion, '>=')
                        && version_compare($infoTo, $fromVersion, '>')
                        && version_compare($infoTo, $toVersion, '<=')
                        && version_compare($infoFrom, $toVersion, '<')
                    ) {
                        $arrRes[] = [
                            'toVersion' => $infoTo,
                            'fileName'  => $file
                        ];
                    }
                }
                break;

            default:
                break;
        }
        return $arrRes;
    }

    /**
     * Set table prefix
     *
     * @param string $tablePrefix
     * @return void
     */
    public function setTablePrefix($tablePrefix)
    {
        parent::setTablePrefix($tablePrefix);
        $this->resource->setTablePrefix($this->tablePrefix);
    }
}
