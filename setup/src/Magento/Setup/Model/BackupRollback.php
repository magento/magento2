<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class to deal with backup and rollback functionality for DB and Code
 */
class BackupRollback
{
    /**
     * Default backup directory
     */
    const DEFAULT_BACKUP_DIRECTORY = 'backups';

    /**
     * Filesystem Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     */
    public function __construct(DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
    }

    /**
     * Take backup for code base
     *
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $log
     * @return void
     */
    public function codeBackup(ObjectManagerInterface $objectManager, LoggerInterface $log)
    {
        /** @var \Magento\Framework\Backup\Filesystem $fsBackup */
        $fsBackup = $objectManager->create('Magento\Framework\Backup\Filesystem');
        $fsBackup->setRootDir($this->directoryList->getRoot());
        $fsBackup->addIgnorePaths($this->getIgnorePaths());
        $backupsDir = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/' . self::DEFAULT_BACKUP_DIRECTORY;
        if (!file_exists($backupsDir)) {
            mkdir($backupsDir);
            chmod($backupsDir, 0777);
        }
        $fsBackup->setBackupsDir($backupsDir);
        $fsBackup->setBackupExtension('tgz');
        $fsBackup->setTime(time());
        $fsBackup->create();
        $log->log('Code backup filename: ' . $fsBackup->getBackupFilename()
            . ' (The archive can be uncompressed with 7-Zip on Windows systems.)');
        $log->log('Code backup path: ' . $fsBackup->getBackupPath());
        $log->logSuccess('Code backup is completed successfully.');
    }

    /**
     * Rollback code base
     *
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $log
     * @param string $rollbackFile
     * @return void
     * @throws LocalizedException
     */
    public function codeRollback(ObjectManagerInterface $objectManager, LoggerInterface $log, $rollbackFile)
    {
        $backupsDir = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/' . self::DEFAULT_BACKUP_DIRECTORY;
        if (!file_exists($backupsDir . '/' . $rollbackFile)) {
            throw new LocalizedException(new \Magento\Framework\Phrase("The rollback file does not exist."));
        }
        /** @var Helper $checkWritable */
        $checkWritable = $objectManager->create('Magento\Framework\Backup\Filesystem\Helper');
        $filesInfo = $checkWritable->getInfo(
            $this->directoryList->getRoot(),
            Helper::INFO_WRITABLE,
            $this->getIgnorePaths()
        );
        if (!$filesInfo['writable']) {
            throw new \Magento\Framework\Backup\Exception\NotEnoughPermissions(
                new \Magento\Framework\Phrase('Unable to make rollback because not all files are writable')
            );
        }
        /** @var \Magento\Framework\Backup\Filesystem $fsRollback */
        $fsRollback = $objectManager->create('Magento\Framework\Backup\Filesystem');
        $fsRollback->setRootDir($this->directoryList->getRoot());
        $fsRollback->addIgnorePaths($this->getIgnorePaths());

        $fsRollback->setBackupsDir($backupsDir);
        $fsRollback->setBackupExtension('tgz');
        $time = explode('_', $rollbackFile);
        $fsRollback->setTime($time[0]);
        $fsRollback->rollback();
        $log->log('Code rollback filename: ' . $fsRollback->getBackupFilename());
        $log->log('Code rollback file path: ' . $fsRollback->getBackupPath());
        $log->logSuccess('Code rollback is completed successfully.');
    }

    /**
     * Get paths that should be excluded during iterative searches for locations
     *
     * @return array
     */
    private function getIgnorePaths()
    {
        return [
            $this->directoryList->getPath(DirectoryList::STATIC_VIEW),
            $this->directoryList->getPath(DirectoryList::VAR_DIR),
            $this->directoryList->getRoot() . '/.idea',
            $this->directoryList->getRoot() . '/.svn',
            $this->directoryList->getRoot() . '/.git'
        ];
    }
}
