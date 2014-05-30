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

/**
 * Class to work with full filesystem and database backups
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Backup;

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
     * @var \Magento\Framework\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @var Factory
     */
    protected $_backupFactory;

    /**
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param Factory $backupFactory
     */
    public function __construct(\Magento\Framework\App\Filesystem $filesystem, Factory $backupFactory)
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
        return $this->_backupFactory->create(
            Factory::TYPE_DB
        )->setBackupExtension(
            'gz'
        )->setTime(
            $this->getTime()
        )->setBackupsDir(
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::VAR_DIR)
        )->setResourceModel(
            $this->getResourceModel()
        );
    }

    /**
     * Get database backup manager
     *
     * @return Db
     */
    protected function _getDbBackupManager()
    {
        if (is_null($this->_dbBackupManager)) {
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
