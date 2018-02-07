<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup;

/**
 * Class to work with database backups
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Db extends AbstractBackup
{
    /**
     * @var \Magento\Framework\Backup\Db\BackupFactory
     */
    protected $_backupFactory;

    /**
     * @param \Magento\Framework\Backup\Db\BackupFactory $backupFactory
     */
    public function __construct(\Magento\Framework\Backup\Db\BackupFactory $backupFactory)
    {
        $this->_backupFactory = $backupFactory;
    }

    /**
     * Implements Rollback functionality for Db
     *
     * @return bool
     */
    public function rollback()
    {
        set_time_limit(0);
        ignore_user_abort(true);

        $this->_lastOperationSucceed = false;

        $archiveManager = new \Magento\Framework\Archive();
        $source = $archiveManager->unpack($this->getBackupPath(), $this->getBackupsDir());

        $file = new \Magento\Framework\Backup\Filesystem\Iterator\File($source);
        foreach ($file as $statement) {
            $this->getResourceModel()->runCommand($statement);
        }
        @unlink($source);

        $this->_lastOperationSucceed = true;

        return true;
    }

    /**
     * Checks whether the line is last in sql command
     *
     * @param string $line
     * @return bool
     */
    protected function _isLineLastInCommand($line)
    {
        $cleanLine = trim($line);
        $lineLength = strlen($cleanLine);

        $returnResult = false;
        if ($lineLength > 0) {
            $lastSymbolIndex = $lineLength - 1;
            if ($cleanLine[$lastSymbolIndex] == ';') {
                $returnResult = true;
            }
        }

        return $returnResult;
    }

    /**
     * Implements Create Backup functionality for Db
     *
     * @return bool
     */
    public function create()
    {
        set_time_limit(0);
        ignore_user_abort(true);

        $this->_lastOperationSucceed = false;

        $backup = $this->_backupFactory->createBackupModel()->setTime(
            $this->getTime()
        )->setType(
            $this->getType()
        )->setPath(
            $this->getBackupsDir()
        )->setName(
            $this->getName()
        );

        $backupDb = $this->_backupFactory->createBackupDbModel();
        $backupDb->createBackup($backup);

        $this->_lastOperationSucceed = true;

        return true;
    }

    /**
     * Get database size
     *
     * @return int
     */
    public function getDBSize()
    {
        $backupDb = $this->_backupFactory->createBackupDbModel();
        return $backupDb->getDBBackupSize();
    }

    /**
     * Get Backup Type
     *
     * @return string
     */
    public function getType()
    {
        return 'db';
    }
}
