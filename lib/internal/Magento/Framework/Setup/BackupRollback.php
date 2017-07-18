<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Backup\Exception\NotEnoughPermissions;
use Magento\Framework\Backup\Filesystem;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;

/**
 * Class to deal with backup and rollback functionality for database and Code base
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackupRollback
{
    /**
     * Default backup directory
     */
    const DEFAULT_BACKUP_DIRECTORY = 'backups';

    /**
     * Path to backup folder
     *
     * @var string
     */
    private $backupsDir;

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
     * Filesystem Helper
     *
     * @var Helper
     */
    private $fsHelper;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $log
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Helper $fsHelper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        LoggerInterface $log,
        DirectoryList $directoryList,
        File $file,
        Helper $fsHelper
    ) {
        $this->objectManager = $objectManager;
        $this->log = $log;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->fsHelper = $fsHelper;
        $this->backupsDir = $this->directoryList->getPath(DirectoryList::VAR_DIR)
            . '/' . self::DEFAULT_BACKUP_DIRECTORY;
    }

    /**
     * Take backup for code base
     *
     * @param int $time
     * @param string $type
     * @return string
     * @throws LocalizedException
     */
    public function codeBackup($time, $type = Factory::TYPE_FILESYSTEM)
    {
        /** @var \Magento\Framework\Backup\Filesystem $fsBackup */
        $fsBackup = $this->objectManager->create(\Magento\Framework\Backup\Filesystem::class);
        $fsBackup->setRootDir($this->directoryList->getRoot());
        if ($type === Factory::TYPE_FILESYSTEM) {
            $fsBackup->addIgnorePaths($this->getCodeBackupIgnorePaths());
            $granularType = 'Code';
            $fsBackup->setName('code');
        } elseif ($type === Factory::TYPE_MEDIA) {
            $fsBackup->addIgnorePaths($this->getMediaBackupIgnorePaths());
            $granularType = 'Media';
            $fsBackup->setName('media');
        } else {
            throw new LocalizedException(new Phrase("This backup type \'$type\' is not supported."));
        }
        if (!$this->file->isExists($this->backupsDir)) {
            $this->file->createDirectory($this->backupsDir);
        }
        $fsBackup->setBackupsDir($this->backupsDir);
        $fsBackup->setBackupExtension('tgz');
        $fsBackup->setTime($time);
        $this->log->log($granularType . ' backup is starting...');
        $fsBackup->create();
        $this->log->log(
            $granularType. ' backup filename: ' . $fsBackup->getBackupFilename()
            . ' (The archive can be uncompressed with 7-Zip on Windows systems)'
        );
        $this->log->log($granularType . ' backup path: ' . $fsBackup->getBackupPath());
        $this->log->logSuccess($granularType . ' backup completed successfully.');
        return $fsBackup->getBackupPath();
    }

    /**
     * Roll back code base
     *
     * @param string $rollbackFile
     * @param string $type
     * @return void
     * @throws LocalizedException
     */
    public function codeRollback($rollbackFile, $type = Factory::TYPE_FILESYSTEM)
    {
        if (preg_match('/[0-9]_(filesystem)_(code|media)\.(tgz)$/', $rollbackFile) !== 1) {
            throw new LocalizedException(new Phrase('Invalid rollback file.'));
        }
        if (!$this->file->isExists($this->backupsDir . '/' . $rollbackFile)) {
            throw new LocalizedException(new Phrase('The rollback file does not exist.'));
        }
        /** @var \Magento\Framework\Backup\Filesystem $fsRollback */
        $fsRollback = $this->objectManager->create(\Magento\Framework\Backup\Filesystem::class);
        if ($type === Factory::TYPE_FILESYSTEM) {
            $ignorePaths = $this->getCodeBackupIgnorePaths();
            $granularType = 'Code';
            $fsRollback->setName('code');
        } elseif ($type === Factory::TYPE_MEDIA) {
            $ignorePaths = $this->getMediaBackupIgnorePaths();
            $granularType = 'Media';
            $fsRollback->setName('media');
        } else {
            throw new LocalizedException(new Phrase("This backup type '$type' is not supported."));
        }
        $filesInfo = $this->fsHelper->getInfo(
            $this->directoryList->getRoot(),
            Helper::INFO_WRITABLE,
            $ignorePaths
        );
        if (!$filesInfo['writable']) {
            throw new NotEnoughPermissions(
                new Phrase('Unable to make rollback because not all files are writable')
            );
        }
        $fsRollback->setRootDir($this->directoryList->getRoot());
        $fsRollback->addIgnorePaths($ignorePaths);
        $fsRollback->setBackupsDir($this->backupsDir);
        $fsRollback->setBackupExtension('tgz');
        $time = explode('_', $rollbackFile);
        $fsRollback->setTime($time[0]);
        $this->log->log($granularType . ' rollback is starting ...');
        $fsRollback->rollback();
        $this->log->log($granularType . ' rollback filename: ' . $fsRollback->getBackupFilename());
        $this->log->log($granularType . ' rollback file path: ' . $fsRollback->getBackupPath());
        $this->log->logSuccess($granularType . ' rollback completed successfully.');
    }

    /**
     * Take backup for database
     *
     * @param int $time
     * @return string
     */
    public function dbBackup($time)
    {
        /** @var \Magento\Framework\Backup\Db $dbBackup */
        $dbBackup = $this->objectManager->create(\Magento\Framework\Backup\Db::class);
        $dbBackup->setRootDir($this->directoryList->getRoot());
        if (!$this->file->isExists($this->backupsDir)) {
            $this->file->createDirectory($this->backupsDir);
        }
        $dbBackup->setBackupsDir($this->backupsDir);
        $dbBackup->setBackupExtension('sql');
        $dbBackup->setTime($time);
        $this->log->log('DB backup is starting...');
        $dbBackup->create();
        $this->log->log('DB backup filename: ' . $dbBackup->getBackupFilename());
        $this->log->log('DB backup path: ' . $dbBackup->getBackupPath());
        $this->log->logSuccess('DB backup completed successfully.');
        return $dbBackup->getBackupPath();
    }

    /**
     * Roll back database
     *
     * @param string $rollbackFile
     * @return void
     * @throws LocalizedException
     */
    public function dbRollback($rollbackFile)
    {
        if (preg_match('/[0-9]_(db)(.*?).(sql)$/', $rollbackFile) !== 1) {
            throw new LocalizedException(new Phrase('Invalid rollback file.'));
        }
        if (!$this->file->isExists($this->backupsDir . '/' . $rollbackFile)) {
            throw new LocalizedException(new Phrase('The rollback file does not exist.'));
        }
        /** @var \Magento\Framework\Backup\Db $dbRollback */
        $dbRollback = $this->objectManager->create(\Magento\Framework\Backup\Db::class);
        $dbRollback->setRootDir($this->directoryList->getRoot());
        $dbRollback->setBackupsDir($this->backupsDir);
        $dbRollback->setBackupExtension('sql');
        $time = explode('_', $rollbackFile);
        if (count($time) === 3) {
            $thirdPart = explode('.', $time[2]);
            $dbRollback->setName($thirdPart[0]);
        }
        $dbRollback->setTime($time[0]);
        $this->log->log('DB rollback is starting...');
        $dbRollback->setResourceModel($this->objectManager->create(\Magento\Backup\Model\ResourceModel\Db::class));
        $dbRollback->rollback();
        $this->log->log('DB rollback filename: ' . $dbRollback->getBackupFilename());
        $this->log->log('DB rollback path: ' . $dbRollback->getBackupPath());
        $this->log->logSuccess('DB rollback completed successfully.');
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
            $this->directoryList->getRoot() . '/update',
            $this->directoryList->getRoot() . '/node_modules',
            $this->directoryList->getRoot() . '/.grunt',
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

    /**
     * Get disk availability for filesystem backup
     *
     * @param string $type
     * @return int
     * @throws LocalizedException
     */
    public function getFSDiskSpace($type = Factory::TYPE_FILESYSTEM)
    {
        $filesystemSize = 0;
        if ($type === Factory::TYPE_FILESYSTEM) {
            $ignorePaths = $this->getCodeBackupIgnorePaths();
        } elseif ($type === Factory::TYPE_MEDIA) {
            $ignorePaths = $this->getMediaBackupIgnorePaths();
        } else {
            throw new LocalizedException(new Phrase("This backup type '$type' is not supported."));
        }
        $filesInfo = $this->fsHelper->getInfo(
            $this->directoryList->getRoot(),
            Helper::INFO_SIZE,
            $ignorePaths
        );
        if ($filesInfo['size']) {
            $filesystemSize = $filesInfo['size'];
        }
        return $filesystemSize;
    }

    /**
     * Get disk availability for database backup
     *
     * @return int
     * @throws LocalizedException
     */
    public function getDBDiskSpace()
    {
        /** @var \Magento\Framework\Backup\Db $dbBackup */
        $dbBackup = $this->objectManager->create(\Magento\Framework\Backup\Db::class);
        return $dbBackup->getDBSize();
    }
}
