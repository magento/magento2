<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup;

/**
 * Class to work with filesystem backups
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     * Ftp host
     *
     * @var string
     */
    protected $_ftpHost;

    /**
     * Ftp username
     *
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
     * Implementation Rollback functionality for Filesystem
     *
     * @throws \Magento\Framework\Exception
     * @return bool
     */
    public function rollback()
    {
        $this->_lastOperationSucceed = false;

        set_time_limit(0);
        ignore_user_abort(true);

        $rollbackWorker = $this->_useFtp ? new \Magento\Framework\Backup\Filesystem\Rollback\Ftp(
            $this
        ) : new \Magento\Framework\Backup\Filesystem\Rollback\Fs(
            $this
        );
        $rollbackWorker->run();

        $this->_lastOperationSucceed = true;
        return $this->_lastOperationSucceed;
    }

    /**
     * Implementation Create Backup functionality for Filesystem
     *
     * @throws \Magento\Framework\Exception
     * @return boolean
     */
    public function create()
    {
        set_time_limit(0);
        ignore_user_abort(true);

        $this->_lastOperationSucceed = false;

        $this->_checkBackupsDir();

        $fsHelper = new \Magento\Framework\Backup\Filesystem\Helper();

        $filesInfo = $fsHelper->getInfo(
            $this->getRootDir(),
            \Magento\Framework\Backup\Filesystem\Helper::INFO_READABLE | \Magento\Framework\Backup\Filesystem\Helper::INFO_SIZE,
            $this->getIgnorePaths()
        );

        if (!$filesInfo['readable']) {
            throw new \Magento\Framework\Backup\Exception\NotEnoughPermissions(
                'Not enough permissions to read files for backup'
            );
        }

        $freeSpace = disk_free_space($this->getBackupsDir());

        if (2 * $filesInfo['size'] > $freeSpace) {
            throw new \Magento\Framework\Backup\Exception\NotEnoughFreeSpace('Not enough free space to create backup');
        }

        $tarTmpPath = $this->_getTarTmpPath();

        $tarPacker = new \Magento\Framework\Backup\Archive\Tar();
        $tarPacker->setSkipFiles($this->getIgnorePaths())->pack($this->getRootDir(), $tarTmpPath, true);

        if (!is_file($tarTmpPath) || filesize($tarTmpPath) == 0) {
            throw new \Magento\Framework\Exception('Failed to create backup');
        }

        $backupPath = $this->getBackupPath();

        $gzPacker = new \Magento\Framework\Archive\Gz();
        $gzPacker->pack($tarTmpPath, $backupPath);

        if (!is_file($backupPath) || filesize($backupPath) == 0) {
            throw new \Magento\Framework\Exception('Failed to create backup');
        }

        @unlink($tarTmpPath);

        $this->_lastOperationSucceed = true;
        return $this->_lastOperationSucceed;
    }

    /**
     * Force class to use ftp for rollback procedure
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $path
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
     * @return $this
     *
     * @see AbstractBackup::setBackupsDir()
     */
    public function setBackupsDir($backupsDir)
    {
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
     * @throws \Magento\Framework\Exception
     */
    protected function _checkBackupsDir()
    {
        $backupsDir = $this->getBackupsDir();

        if (!is_dir($backupsDir)) {
            $backupsDirParentDirectory = basename($backupsDir);

            if (!is_writeable($backupsDirParentDirectory)) {
                throw new \Magento\Framework\Backup\Exception\NotEnoughPermissions('Cant create backups directory');
            }

            mkdir($backupsDir);
            chmod($backupsDir, 0777);
        }

        if (!is_writable($backupsDir)) {
            throw new \Magento\Framework\Backup\Exception\NotEnoughPermissions('Backups directory is not writeable');
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
}
