<?php
/**
 * Resource Setup Model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Setup\Module\Setup\SetupCache;

class DataSetup extends \Magento\Framework\Module\Setup implements ModuleDataSetupInterface
{
    /**
     * Tables data cache
     *
     * @var SetupCache
     */
    private $setupCache;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_eventManager;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * Migration factory
     *
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
     * Init
     *
     * @param \Magento\Framework\Module\Setup\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Module\Setup\Context $context,
        $connectionName = ModuleDataSetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        parent::__construct($context->getResourceModel(), $connectionName);
        $this->_eventManager = $context->getEventManager();
        $this->_logger = $context->getLogger();
        $this->_migrationFactory = $context->getMigrationFactory();
        $this->filesystem = $context->getFilesystem();
        $this->setupCache = new SetupCache();
    }

    /**
     * {@inheritdoc}
     */
    public function getSetupCache()
    {
        return $this->setupCache;
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
        if (!$this->setupCache->has($table, $parentId, $rowId)) {
            $connection = $this->getConnection();
            $bind = ['id_field' => $rowId];
            $select = $connection->select()
                ->from($table)
                ->where($connection->quoteIdentifier($idField) . '= :id_field');
            if (null !== $parentField) {
                $select->where($connection->quoteIdentifier($parentField) . '= :parent_id');
                $bind['parent_id'] = $parentId;
            }
            $this->setupCache->setRow($table, $parentId, $rowId, $connection->fetchRow($select, $bind));
        }

        return $this->setupCache->get($table, $parentId, $rowId, $field);
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
        $connection = $this->getConnection();
        $where = [$connection->quoteIdentifier($idField) . '=?' => $rowId];
        if (null !== $parentField) {
            $where[$connection->quoteIdentifier($parentField) . '=?'] = $parentId;
        }

        $connection->delete($table, $where);

        $this->setupCache->remove($table, $parentId, $rowId);

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

        $connection = $this->getConnection();
        $where = [$connection->quoteIdentifier($idField) . '=?' => $rowId];
        $connection->update($table, $data, $where);

        if (is_array($field)) {
            $oldRow = $this->setupCache->has($table, $parentId, $rowId) ?
                $this->setupCache->get($table, $parentId, $rowId) :
                [];
            $newRowData = array_merge($oldRow, $field);
            $this->setupCache->setRow($table, $parentId, $rowId, $newRowData);
        } else {
            $this->setupCache->setField($table, $parentId, $rowId, $field, $value);
        }

        return $this;
    }

    /**
     * Gets event manager
     *
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * Gets filesystem
     *
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
