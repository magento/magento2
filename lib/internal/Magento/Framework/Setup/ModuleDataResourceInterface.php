<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * DB data resource interface for a module
 */
interface ModuleDataResourceInterface extends ResourceInterface
{
    const DEFAULT_SETUP_CONNECTION = 'core_setup';

    const VERSION_COMPARE_EQUAL = 0;

    const VERSION_COMPARE_LOWER = -1;

    const VERSION_COMPARE_GREATER = 1;

    const TYPE_DATA_INSTALL = 'data-install';

    const TYPE_DATA_UPGRADE = 'data-upgrade';

    /**
     * Apply data updates to the system after upgrading.
     *
     * @return $this
     */
    public function applyDataUpdates();

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
    public function getTableRow($table, $idField, $rowId, $field = null, $parentField = null, $parentId = 0);

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
    public function deleteTableRow($table, $idField, $rowId, $parentField = null, $parentId = 0);

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
    public function updateTableRow($table, $idField, $rowId, $field, $value = null, $parentField = null, $parentId = 0);

    /**
     * Check call afterApplyAllUpdates method for setup class
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCallAfterApplyAllUpdates();

    /**
     * Run each time after applying of all updates,
     * if setup model setted $_callAfterApplyAllUpdates flag to true
     *
     * @return $this
     */
    public function afterApplyAllUpdates();

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager();

    /**
     * @return \Magento\Framework\Filesystem
     */
    public function getFilesystem();

    /**
     * Create migration setup
     *
     * @param array $data
     * @return \Magento\Framework\Module\Setup\Migration
     */
    public function createMigrationSetup(array $data = []);
}