<?php
/**
 * Resource Setup Model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Setup\ModuleDataResourceInterface;

class DataSetup extends \Magento\Framework\Module\Setup implements ModuleDataResourceInterface
{
    /**
     * Setup resource name
     * @var string
     */
    private $_resourceName;

    /**
     * Setup module name
     * @var string
     */
    private $moduleName;

    /**
     * Setup module configuration object
     *
     * @var array
     */
    private $_moduleConfig;

    /**
     * Call afterApplyAllUpdates method flag
     *
     * @var boolean
     */
    private $_callAfterApplyAllUpdates = false;

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
    private $_modulesReader;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_eventManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */
    private $_resource;

    /**
     * @var \Magento\Framework\Module\Setup\MigrationFactory
     */
    private $_migrationFactory;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $modulesDir;

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
        $connectionName = ModuleDataResourceInterface::DEFAULT_SETUP_CONNECTION
    ) {
        parent::__construct($context->getResourceModel(), $connectionName);
        $this->_eventManager = $context->getEventManager();
        $this->_logger = $context->getLogger();
        $this->_modulesReader = $context->getModulesReader();
        $this->_resourceName = $resourceName;
        $this->moduleName = $moduleName;

        $this->_resource = $context->getResource();
        $this->_migrationFactory = $context->getMigrationFactory();
        $this->_moduleConfig = $context->getModuleList()->getOne($moduleName);
        $this->filesystem = $context->getFilesystem();
        $this->modulesDir = $this->filesystem->getDirectoryRead(DirectoryList::MODULES);
    }

    /**
     * {@inheritdoc}
     */
    public function getSetupCache()
    {
        return $this->_setupCache;
    }

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
        $data['setup'] = $this;
        return $this->_migrationFactory->create($data);
    }
}
