<?php
/**
 * Resource Setup Model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\App\Filesystem\DirectoryList;

class DataSetup extends \Magento\Framework\Module\Setup implements \Magento\Framework\Module\Updater\SetupInterface
{
    /**
     * Setup resource name
     * @var string
     */
    protected $_resourceName;

    /**
     * Setup module configuration object
     *
     * @var array
     */
    protected $_moduleConfig;

    /**
     * Call afterApplyAllUpdates method flag
     *
     * @var boolean
     */
    protected $_callAfterApplyAllUpdates = false;

    /**
     * Tables data cache array
     *
     * @var array
     */
    protected $_setupCache = [];

    /**
     * Modules configuration reader
     *
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_modulesReader;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Module\Setup\MigrationFactory
     */
    protected $_migrationFactory;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $modulesDir;

    /**
     * @param \Magento\Framework\Module\Setup\Context $context
     * @param string $resourceName
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Module\Setup\Context $context,
        $resourceName,
        $moduleName,
        $connectionName = \Magento\Framework\Module\Updater\SetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        parent::__construct($context->getResourceModel(), $connectionName);
        $this->_eventManager = $context->getEventManager();
        $this->_logger = $context->getLogger();
        $this->_modulesReader = $context->getModulesReader();
        $this->_resourceName = $resourceName;
        $this->_resource = $context->getResource();
        $this->_migrationFactory = $context->getMigrationFactory();
        $this->_moduleConfig = $context->getModuleList()->getOne($moduleName);
        $this->filesystem = $context->getFilesystem();
        $this->modulesDir = $this->filesystem->getDirectoryRead(DirectoryList::MODULES);
    }

    /**
     * Apply data updates to the system after upgrading.
     *
     * @return $this
     */
    public function applyDataUpdates()
    {
        $dataVer = $this->_resource->getDataVersion($this->_resourceName);
        $configVer = $this->_moduleConfig['schema_version'];
        if ($dataVer !== false) {
            $status = version_compare($configVer, $dataVer);
            if ($status == self::VERSION_COMPARE_GREATER) {
                $this->_upgradeData($dataVer, $configVer);
            }
        } elseif ($configVer) {
            $this->_installData($configVer);
        }
        return $this;
    }

    /**
     * Run data install scripts
     *
     * @param string $newVersion
     * @return $this
     */
    protected function _installData($newVersion)
    {
        $oldVersion = $this->_modifyResourceDb(self::TYPE_DATA_INSTALL, '', $newVersion);
        $this->_modifyResourceDb(self::TYPE_DATA_UPGRADE, $oldVersion, $newVersion);
        $this->_resource->setDataVersion($this->_resourceName, $newVersion);

        return $this;
    }

    /**
     * Run data upgrade scripts
     *
     * @param string $oldVersion
     * @param string $newVersion
     * @return $this
     */
    protected function _upgradeData($oldVersion, $newVersion)
    {
        $this->_modifyResourceDb('data-upgrade', $oldVersion, $newVersion);
        $this->_resource->setDataVersion($this->_resourceName, $newVersion);

        return $this;
    }

    /**
     * Retrieve available Data install/upgrade files for current module
     *
     * @param string $actionType
     * @param string $fromVersion
     * @param string $toVersion
     * @return array
     */
    protected function _getAvailableDataFiles($actionType, $fromVersion, $toVersion)
    {
        $modName = (string)$this->_moduleConfig['name'];
        $files = [];

        $filesDir = $this->_modulesReader->getModuleDir('data', $modName) . '/' . $this->_resourceName;
        $modulesDirPath = $this->modulesDir->getRelativePath($filesDir);
        if ($this->modulesDir->isDirectory($modulesDirPath) && $this->modulesDir->isReadable($modulesDirPath)) {
            $regExp = sprintf('#%s-(.*)\.php$#i', $actionType);
            foreach ($this->modulesDir->read($modulesDirPath) as $file) {
                $matches = [];
                if (preg_match($regExp, $file, $matches)) {
                    $files[$matches[1]] = $this->modulesDir->getAbsolutePath($file);
                }
            }
        }

        if (empty($files)) {
            return [];
        }

        return $this->_getModifySqlFiles($actionType, $fromVersion, $toVersion, $files);
    }

    /**
     * Run module modification files. Return version of last applied upgrade (false if no upgrades applied)
     * @param string $actionType
     * @param string $fromVersion
     * @param string $toVersion
     * @return false|string
     * @throws \Magento\Framework\Exception
     */
    protected function _modifyResourceDb($actionType, $fromVersion, $toVersion)
    {
        $files = $this->_getAvailableDataFiles($actionType, $fromVersion, $toVersion);
        if (empty($files) || !$this->getConnection()) {
            return false;
        }

        $version = false;

        foreach ($files as $file) {
            $fileName = $file['fileName'];
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
            $this->getConnection()->disallowDdlCache();
            try {
                switch ($fileType) {
                    case 'php':
                        $result = $this->_includeFile($fileName);
                        break;
                    case 'sql':
                        $sql = $this->modulesDir->readFile($this->modulesDir->getRelativePath($fileName));
                        if (!empty($sql)) {
                            $result = $this->run($sql);
                        } else {
                            $result = true;
                        }
                        break;
                    default:
                        $result = false;
                        break;
                }

                if ($result) {
                    $this->_resource->setDataVersion($this->_resourceName, $file['toVersion']);
                    $this->_logger->info($fileName);
                } else {
                    $this->_logger->info("Failed resource setup: {$fileName}");
                }
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception(
                    sprintf('Error in file: "%s" - %s', $fileName, $e->getMessage()),
                    0,
                    $e
                );
            }
            $version = $file['toVersion'];
            $this->getConnection()->allowDdlCache();
        }
        return $version;
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
    protected function _includeFile($fileName)
    {
        return include $fileName;
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
    protected function _getModifySqlFiles($actionType, $fromVersion, $toVersion, $arrFiles)
    {
        $arrRes = [];
        switch ($actionType) {
            case self::TYPE_DATA_INSTALL:
                uksort($arrFiles, 'version_compare');
                foreach ($arrFiles as $version => $file) {
                    if (version_compare($version, $toVersion) !== self::VERSION_COMPARE_GREATER) {
                        $arrRes[0] = [
                            'toVersion' => $version,
                            'fileName'  => $file,
                        ];
                    }
                }
                break;

            case self::TYPE_DATA_UPGRADE:
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

    /******************* UTILITY METHODS *****************/

    /**
     * Retrieve row or field from table by id or string and parent id
     *
     * @param string $table
     * @param string $idField
     * @param string|integer $rowId
     * @param string|null $field
     * @param string|null $parentField
     * @param string|integer $parentId
     * @return mixed
     */
    public function getTableRow($table, $idField, $rowId, $field = null, $parentField = null, $parentId = 0)
    {
        $table = $this->getTable($table);
        if (empty($this->_setupCache[$table][$parentId][$rowId])) {
            $adapter = $this->getConnection();
            $bind = ['id_field' => $rowId];
            $select = $adapter->select()->from($table)->where($adapter->quoteIdentifier($idField) . '= :id_field');
            if (null !== $parentField) {
                $select->where($adapter->quoteIdentifier($parentField) . '= :parent_id');
                $bind['parent_id'] = $parentId;
            }
            $this->_setupCache[$table][$parentId][$rowId] = $adapter->fetchRow($select, $bind);
        }

        if (null === $field) {
            return $this->_setupCache[$table][$parentId][$rowId];
        }
        return isset(
            $this->_setupCache[$table][$parentId][$rowId][$field]
        ) ? $this->_setupCache[$table][$parentId][$rowId][$field] : false;
    }

    /**
     * Delete table row
     *
     * @param string $table
     * @param string $idField
     * @param string|int $rowId
     * @param null|string $parentField
     * @param int|string $parentId
     * @return $this
     */
    public function deleteTableRow($table, $idField, $rowId, $parentField = null, $parentId = 0)
    {
        $table = $this->getTable($table);
        $adapter = $this->getConnection();
        $where = [$adapter->quoteIdentifier($idField) . '=?' => $rowId];
        if (!is_null($parentField)) {
            $where[$adapter->quoteIdentifier($parentField) . '=?'] = $parentId;
        }

        $adapter->delete($table, $where);

        if (isset($this->_setupCache[$table][$parentId][$rowId])) {
            unset($this->_setupCache[$table][$parentId][$rowId]);
        }

        return $this;
    }

    /**
     * Update one or more fields of table row
     *
     * @param string $table
     * @param string $idField
     * @param string|integer $rowId
     * @param string|array $field
     * @param mixed|null $value
     * @param string $parentField
     * @param string|integer $parentId
     * @return $this
     */
    public function updateTableRow($table, $idField, $rowId, $field, $value = null, $parentField = null, $parentId = 0)
    {
        $table = $this->getTable($table);
        if (is_array($field)) {
            $data = $field;
        } else {
            $data = [$field => $value];
        }

        $adapter = $this->getConnection();
        $where = [$adapter->quoteIdentifier($idField) . '=?' => $rowId];
        $adapter->update($table, $data, $where);

        if (isset($this->_setupCache[$table][$parentId][$rowId])) {
            if (is_array($field)) {
                $this->_setupCache[$table][$parentId][$rowId] = array_merge(
                    $this->_setupCache[$table][$parentId][$rowId],
                    $field
                );
            } else {
                $this->_setupCache[$table][$parentId][$rowId][$field] = $value;
            }
        }

        return $this;
    }

    /**
     * Check call afterApplyAllUpdates method for setup class
     *
     * @return bool
     */
    public function getCallAfterApplyAllUpdates()
    {
        return $this->_callAfterApplyAllUpdates;
    }

    /**
     * Run each time after applying of all updates,
     * if setup model setted $_callAfterApplyAllUpdates flag to true
     *
     * @return $this
     */
    public function afterApplyAllUpdates()
    {
        return $this;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \Magento\Framework\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Create migration setup
     *
     * @param array $data
     * @return \Magento\Framework\Module\Setup\Migration
     */
    public function createMigrationSetup(array $data = [])
    {
        return $this->_migrationFactory->create($data);
    }
}
