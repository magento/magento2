<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Backup;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Archive\Gz;
use Magento\Framework\Backup\Archive\Tar;
use Magento\Framework\Backup\Exception\NotEnoughFreeSpace;
use Magento\Framework\Backup\Exception\NotEnoughPermissions;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Backup\Filesystem\Rollback\Fs;
use Magento\Framework\Backup\Filesystem\Rollback\Ftp;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class to work with filesystem backups
 */
class Filesystem extends AbstractBackup
{
    /**
     * Paths that ignored when creating or rolling back snapshot
     *
     * @var array
     */
    protected $_ignorePaths = [];

    /**
     * Whether use ftp account for rollback procedure
     *
     * @var bool
     */
    protected $_useFtp = false;

    /**
     * @var string
     */
    protected $_ftpHost;

    /**
     * @var string
     */
    protected $_ftpUser;

    /**
     * Password to ftp account
     *
     * @var string
     */
    protected $_ftpPass;

    /**
     * Ftp path to Magento installation
     *
     * @var string
     */
    protected $_ftpPath;

    /**
     * @var Ftp
     */
    protected $rollBackFtp;

    /**
     * @var Fs
     */
    protected $rollBackFs;

    /**
     * Implementation Rollback functionality for Filesystem
     *
     * @throws LocalizedException
     * @return bool
     */
    public function rollback()
    {
        $this->_lastOperationSucceed = false;

        set_time_limit(0);
        ignore_user_abort(true);

        $rollbackWorker = $this->_useFtp ? $this->getRollBackFtp() : $this->getRollBackFs();
        $rollbackWorker->run();

        $this->_lastOperationSucceed = true;
        return $this->_lastOperationSucceed;
    }

    /**
     * Implementation Create Backup functionality for Filesystem
     *
     * @throws LocalizedException
     * @return boolean
     */
    public function create()
    {
        set_time_limit(0);
        ignore_user_abort(true);

        $this->_lastOperationSucceed = false;

        $this->_checkBackupsDir();

        $fsHelper = new Helper();

        $filesInfo = $fsHelper->getInfo(
            $this->getRootDir(),
            Helper::INFO_READABLE |
            Helper::INFO_SIZE,
            $this->getIgnorePaths()
        );

        if (!$filesInfo['readable']) {
            throw new NotEnoughPermissions(
                new Phrase('Not enough permissions to read files for backup')
            );
        }

        $this->validateAvailableDiscSpace($this->getBackupsDir(), $filesInfo['size']);

        $tarTmpPath = $this->_getTarTmpPath();

        $tarPacker = new Tar();
        $tarPacker->setSkipFiles($this->getIgnorePaths())->pack($this->getRootDir(), $tarTmpPath, true);

        if (!is_file($tarTmpPath) || filesize($tarTmpPath) == 0) {
            throw new LocalizedException(
                new Phrase('Failed to create backup')
            );
        }

        $backupPath = $this->getBackupPath();

        $gzPacker = new Gz();
        $gzPacker->pack($tarTmpPath, $backupPath);

        if (!is_file($backupPath) || filesize($backupPath) == 0) {
            throw new LocalizedException(
                new Phrase('Failed to create backup')
            );
        }

        @unlink($tarTmpPath);

        $this->_lastOperationSucceed = true;
        return $this->_lastOperationSucceed;
    }

    /**
     * Validate if disk space is available for creating backup
     *
     * @param string $backupDir
     * @param int $size
     *
     * @return void
     * @throws NotEnoughFreeSpace
     */
    public function validateAvailableDiscSpace($backupDir, $size)
    {
        $freeSpace = disk_free_space($backupDir);
        $requiredSpace = 2 * $size;
        if ($requiredSpace > $freeSpace) {
            throw new NotEnoughFreeSpace(
                new Phrase(
                    'Warning: necessary space for backup is ' . (ceil($requiredSpace) / 1024)
                    . 'MB, but your free disc space is ' . (ceil($freeSpace) / 1024) . 'MB.'
                )
            );
        }
    }

    /**
     * Force class to use ftp for rollback procedure
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $path
     *
     * @return $this
     */
    public function setUseFtp($host, $username, $password, $path)
    {
        $this->_useFtp = true;
        $this->_ftpHost = $host;
        $this->_ftpUser = $username;
        $this->_ftpPass = $password;
        $this->_ftpPath = $path;
        return $this;
    }

    /**
     * Get backup type
     *
     * @return string
     *
     * @see BackupInterface::getType()
     */
    public function getType()
    {
        return 'filesystem';
    }

    /**
     * Add path that should be ignoring when creating or rolling back backup
     *
     * @param string|array $paths
     *
     * @return $this
     */
    public function addIgnorePaths($paths)
    {
        if (is_string($paths)) {
            if (!in_array($paths, $this->_ignorePaths)) {
                $this->_ignorePaths[] = $paths;
            }
        } elseif (is_array($paths)) {
            foreach ($paths as $path) {
                $this->addIgnorePaths($path);
            }
        }

        return $this;
    }

    /**
     * Get paths that should be ignored while creating or rolling back backup procedure
     *
     * @return array
     */
    public function getIgnorePaths()
    {
        return $this->_ignorePaths;
    }

    /**
     * Set directory where backups saved and add it to ignore paths
     *
     * @param string $backupsDir
     *
     * @return $this
     *
     * @see AbstractBackup::setBackupsDir()
     */
    public function setBackupsDir($backupsDir)
    {
        $backupsDir = rtrim($backupsDir, '/');
        parent::setBackupsDir($backupsDir);
        $this->addIgnorePaths($backupsDir);
        return $this;
    }

    /**
     * Getter for $_ftpPath variable
     *
     * @return string
     */
    public function getFtpPath()
    {
        return $this->_ftpPath;
    }

    /**
     * Get ftp connection string
     *
     * @return string
     */
    public function getFtpConnectString()
    {
        return 'ftp://' . $this->_ftpUser . ':' . $this->_ftpPass . '@' . $this->_ftpHost . $this->_ftpPath;
    }

    /**
     * Check backups directory existence and whether it's writeable
     *
     * @return void
     * @throws NotEnoughPermissions
     */
    protected function _checkBackupsDir()
    {
        $backupsDir = $this->getBackupsDir();

        if (!is_dir($backupsDir)) {
            $backupsDirParentDirectory = basename($backupsDir);

            if (!is_writeable($backupsDirParentDirectory)) {
                throw new NotEnoughPermissions(
                    new Phrase('Cant create backups directory')
                );
            }

            mkdir($backupsDir);
            chmod($backupsDir, 0755);
        }

        if (!is_writable($backupsDir)) {
            throw new NotEnoughPermissions(
                new Phrase('Backups directory is not writeable')
            );
        }
    }

    /**
     * Generate tmp name for tarball
     *
     * @return string
     */
    protected function _getTarTmpPath()
    {
        $tmpName = '~tmp-' . microtime(true) . '.tar';
        return $this->getBackupsDir() . '/' . $tmpName;
    }

    /**
     * Get rollback FTP
     *
     * @return Ftp
     * @deprecated 101.0.0
     * @see Nothing
     */
    protected function getRollBackFtp()
    {
        if (!$this->rollBackFtp) {
            $this->rollBackFtp = ObjectManager::getInstance()->create(
                Ftp::class,
                ['snapshotObject' => $this]
            );
        }

        return $this->rollBackFtp;
    }

    /**
     * Get rollback FS
     *
     * @return Fs
     * @deprecated 101.0.0
     * @see Nothing
     */
    protected function getRollBackFs()
    {
        if (!$this->rollBackFs) {
            $this->rollBackFs = ObjectManager::getInstance()->create(
                Fs::class,
                ['snapshotObject' => $this]
            );
        }

        return $this->rollBackFs;
    }
}
