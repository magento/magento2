<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to work with full filesystem and database backups
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Backup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem as AppFilesystem;

/**
 * Class \Magento\Framework\Backup\Snapshot
 *
 */
class Snapshot extends Filesystem
{
    /**
     * Database backup manager
     *
     * @var Db
     */
    protected $_dbBackupManager;

    /**
     * Filesystem facade
     *
     * @var AppFilesystem
     */
    protected $_filesystem;

    /**
     * @var Factory
     */
    protected $_backupFactory;

    /**
     * @param AppFilesystem $filesystem
     * @param Factory $backupFactory
     */
    public function __construct(AppFilesystem $filesystem, Factory $backupFactory)
    {
        $this->_filesystem = $filesystem;
        $this->_backupFactory = $backupFactory;
    }

    /**
     * Implementation Rollback functionality for Snapshot
     *
     * @throws \Exception
     * @return bool
     */
    public function rollback()
    {
        $result = parent::rollback();

        $this->_lastOperationSucceed = false;

        try {
            $this->_getDbBackupManager()->rollback();
        } catch (\Exception $e) {
            $this->_removeDbBackup();
            throw $e;
        }

        $this->_removeDbBackup();
        $this->_lastOperationSucceed = true;

        return $result;
    }

    /**
     * Implementation Create Backup functionality for Snapshot
     *
     * @throws \Exception
     * @return bool
     */
    public function create()
    {
        $this->_getDbBackupManager()->create();

        try {
            $result = parent::create();
        } catch (\Exception $e) {
            $this->_removeDbBackup();
            throw $e;
        }

        $this->_lastOperationSucceed = false;
        $this->_removeDbBackup();
        $this->_lastOperationSucceed = true;

        return $result;
    }

    /**
     * Overlap getType
     *
     * @return string
     * @see BackupInterface::getType()
     */
    public function getType()
    {
        return 'snapshot';
    }

    /**
     * Create Db Instance
     *
     * @return BackupInterface
     */
    protected function _createDbBackupInstance()
    {
        return $this->_backupFactory->create(Factory::TYPE_DB)
            ->setBackupExtension('sql')
            ->setTime($this->getTime())
            ->setBackupsDir($this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->getAbsolutePath())
            ->setResourceModel($this->getResourceModel());
    }

    /**
     * Get database backup manager
     *
     * @return Db
     */
    protected function _getDbBackupManager()
    {
        if ($this->_dbBackupManager === null) {
            $this->_dbBackupManager = $this->_createDbBackupInstance();
        }

        return $this->_dbBackupManager;
    }

    /**
     * Set Db backup manager
     *
     * @param AbstractBackup $manager
     * @return $this
     */
    public function setDbBackupManager(AbstractBackup $manager)
    {
        $this->_dbBackupManager = $manager;
        return $this;
    }

    /**
     * Get Db Backup Filename
     *
     * @return string
     */
    public function getDbBackupFilename()
    {
        return $this->_getDbBackupManager()->getBackupFilename();
    }

    /**
     * Remove Db backup after added it to the snapshot
     *
     * @return $this
     */
    protected function _removeDbBackup()
    {
        @unlink($this->_getDbBackupManager()->getBackupPath());
        return $this;
    }
}
