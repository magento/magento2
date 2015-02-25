<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\Resource;
use Magento\Framework\Module\Updater\SetupInterface;
use Magento\Setup\Model\LoggerInterface;
use Magento\Setup\Module\Setup\FileResolver as SetupFileResolver;

class SetupModule extends Setup
{
    const TYPE_DB_INSTALL = 'install';

    const TYPE_DB_UPGRADE = 'upgrade';

    const TYPE_DB_RECURRING = 'recurring';

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
     * Setup File Resolver
     *
     * @var SetupFileResolver
     */
    protected $fileResolver;

    /**
     * Resource
     *
     * @var Resource
     */
    protected $resource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param LoggerInterface $log
     * @param ModuleListInterface $moduleList
     * @param SetupFileResolver $fileResolver
     * @param string $moduleName
     * @param \Magento\Framework\App\Resource $resource
     * @param string $connectionName
     */
    public function __construct(
        LoggerInterface $log,
        ModuleListInterface $moduleList,
        SetupFileResolver $fileResolver,
        $moduleName,
        \Magento\Framework\App\Resource $resource,
        $connectionName = SetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        parent::__construct($resource, $connectionName);
        $this->logger = $log;
        $this->fileResolver = $fileResolver;
        $this->moduleConfig = $moduleList->getOne($moduleName);
        $this->resource = new Resource($resource);
        $this->resourceName = $this->fileResolver->getResourceCode($moduleName);
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
        foreach ($this->fileResolver->getSqlSetupFiles($moduleName, self::TYPE_DB_RECURRING . '.php') as $file) {
            try {
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
        $dbFiles = [];
        $typeFiles = [];
        $regExpDb = sprintf('#%s-(.*)\.(php|sql)$#i', $actionType);
        $regExpType = sprintf('#%s-%s-(.*)\.(php|sql)$#i', 'mysql4', $actionType);
        foreach ($this->fileResolver->getSqlSetupFiles($moduleName, '*.{php,sql}') as $file) {
            $matches = [];
            if (preg_match($regExpDb, $file, $matches)) {
                $dbFiles[$matches[1]] = $file;
            } elseif (preg_match($regExpType, $file, $matches)) {
                $typeFiles[$matches[1]] = $file;
            }
        }

        if (empty($typeFiles) && empty($dbFiles)) {
            return [];
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
            if (version_compare($configVer, $dbVer, '>')) {
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
                    if (version_compare($version, $toVersion, '<=')) {
                        $arrRes[0] = [
                            'toVersion' => $version,
                            'fileName'  => $file,
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
                            'fileName'  => $file,
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
     * Include file by path
     * This method should perform only file inclusion.
     * Implemented to prevent possibility of changing important and used variables
     * inside the setup model while installing
     *
     * @param string $fileName
     * @return mixed
     */
    private function includeFile($fileName)
    {
        $this->logger->log("Include {$fileName}");
        return include $fileName;
    }
}
