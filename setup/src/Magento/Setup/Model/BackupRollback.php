<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Backup\Exception\NotEnoughPermissions;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;

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
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    private $log;

    /**
     * Filesystem Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * File
     *
     * @var File
     */
    private $file;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $log
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        LoggerInterface $log,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->objectManager = $objectManager;
        $this->log = $log;
        $this->directoryList = $directoryList;
        $this->file = $file;
    }

    /**
     * Take backup for code base
     *
     * @param array $mediaIgnorePaths
     * @return void
     */
    public function codeBackup($mediaIgnorePaths = [])
    {
        /** @var \Magento\Framework\Backup\Filesystem $fsBackup */
        $fsBackup = $this->objectManager->create('Magento\Framework\Backup\Filesystem');
        $fsBackup->setRootDir($this->directoryList->getRoot());
        $type = 'Code';
        if (empty($mediaIgnorePaths)) {
            $fsBackup->addIgnorePaths($this->getCodeBackupIgnorePaths());
        } else {
            $fsBackup->addIgnorePaths($mediaIgnorePaths);
            $type = 'Media';
            $fsBackup->setName('media');
        }
        $backupsDir = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/' . self::DEFAULT_BACKUP_DIRECTORY;
        if (!$this->file->isExists($backupsDir)) {
            $this->file->createDirectory($backupsDir, 0777);
        }
        $fsBackup->setBackupsDir($backupsDir);
        $fsBackup->setBackupExtension('tgz');
        $fsBackup->setTime(time());
        $fsBackup->create();
        $this->log->log(
            $type. ' backup filename: ' . $fsBackup->getBackupFilename()
            . ' (The archive can be uncompressed with 7-Zip on Windows systems)'
        );
        $this->log->log($type . ' backup path: ' . $fsBackup->getBackupPath());
        $this->log->logSuccess($type . ' backup is completed successfully.');
    }

    /**
     * Rollback code base
     *
     * @param string $rollbackFile
     * @return void
     * @throws LocalizedException
     */
    public function codeRollback($rollbackFile)
    {
        $backupsDir = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/' . self::DEFAULT_BACKUP_DIRECTORY;
        if (!$this->file->isExists($backupsDir . '/' . $rollbackFile)) {
            throw new LocalizedException(__("The rollback file does not exist."));
        }
        /** @var Helper $checkWritable */
        $checkWritable = $this->objectManager->create('Magento\Framework\Backup\Filesystem\Helper');
        $filesInfo = $checkWritable->getInfo(
            $this->directoryList->getRoot(),
            Helper::INFO_WRITABLE,
            $this->getCodeBackupIgnorePaths()
        );
        if (!$filesInfo['writable']) {
            throw new NotEnoughPermissions(
                __('Unable to make rollback because not all files are writable')
            );
        }
        /** @var \Magento\Framework\Backup\Filesystem $fsRollback */
        $fsRollback = $this->objectManager->create('Magento\Framework\Backup\Filesystem');
        $fsRollback->setRootDir($this->directoryList->getRoot());
        $fsRollback->addIgnorePaths($this->getCodeBackupIgnorePaths());

        $fsRollback->setBackupsDir($backupsDir);
        $fsRollback->setBackupExtension('tgz');
        $time = explode('_', $rollbackFile);
        $fsRollback->setTime($time[0]);
        $fsRollback->rollback();
        $this->log->log('Code rollback filename: ' . $fsRollback->getBackupFilename());
        $this->log->log('Code rollback file path: ' . $fsRollback->getBackupPath());
        $this->log->logSuccess('Code rollback is completed successfully.');
    }

    /**
     * Take backup for user data
     *
     * @return void
     */
    public function dataBackup()
    {
        // DB Backup
        $areaCode = 'adminhtml';
        /** @var \Magento\Framework\App\State $appState */
        $appState = $this->objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($areaCode);
        /** @var \Magento\Framework\App\ObjectManager\ConfigLoader $configLoader */
        $configLoader = $this->objectManager->get('Magento\Framework\App\ObjectManager\ConfigLoader');
        $this->objectManager->configure($configLoader->load($areaCode));

        /** @var \Magento\Framework\Backup\Db $dbBackup */
        $dbBackup = $this->objectManager->create('Magento\Framework\Backup\Db');
        $dbBackup->setRootDir($this->directoryList->getRoot());
        $backupsDir = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/' . self::DEFAULT_BACKUP_DIRECTORY;
        if (!$this->file->isExists($backupsDir)) {
            $this->file->createDirectory($backupsDir, 0777);
        }
        $dbBackup->setBackupsDir($backupsDir);
        $dbBackup->setBackupExtension('gz');
        $dbBackup->setTime(time());
        $dbBackup->create();
        $this->log->log('DB backup filename: ' . $dbBackup->getBackupFilename()
            . ' (The archive can be uncompressed with 7-Zip on Windows systems)');
        $this->log->log('DB backup path: ' . $dbBackup->getBackupPath());
        $this->log->logSuccess('DB backup is completed successfully.');

        // Media Backup
        $this->codeBackup($this->getMediaBackupIgnorePaths());
    }

    /**
     * Get paths that should be excluded during iterative searches for locations for code backup only
     *
     * @return array
     */
    private function getCodeBackupIgnorePaths()
    {
        return [
            $this->directoryList->getPath(DirectoryList::MEDIA),
            $this->directoryList->getPath(DirectoryList::STATIC_VIEW),
            $this->directoryList->getPath(DirectoryList::VAR_DIR),
            $this->directoryList->getRoot() . '/.idea',
            $this->directoryList->getRoot() . '/.svn',
            $this->directoryList->getRoot() . '/.git'
        ];
    }

    /**
     * Get paths that should be excluded during iterative searches for locations for media backup only
     *
     * @return array
     */
    private function getMediaBackupIgnorePaths()
    {
        $ignorePaths = [];
        foreach (new \DirectoryIterator($this->directoryList->getRoot()) as $item) {
            if (!$item->isDot() && ($this->directoryList->getPath(DirectoryList::PUB) !== $item->getPathname())) {
                $ignorePaths[] = str_replace('\\', '/', $item->getPathname());
            }
        }
        foreach (new \DirectoryIterator($this->directoryList->getPath(DirectoryList::PUB)) as $item) {
            if (!$item->isDot() && ($this->directoryList->getPath(DirectoryList::MEDIA) !== $item->getPathname())) {
                $ignorePaths[] = str_replace('\\', '/', $item->getPathname());
            }
        }
        return $ignorePaths;
    }
}
