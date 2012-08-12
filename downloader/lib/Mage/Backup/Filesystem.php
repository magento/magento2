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
 * @category    Mage
 * @package     Mage_Backup
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work with filesystem backups
 *
 * @category    Mage
 * @package     Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Filesystem extends Mage_Backup_Abstract
{
    /**
     * Paths that ignored when creating or rolling back snapshot
     *
     * @var array
     */
    protected $_ignorePaths = array();

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
     * @throws Mage_Exception
     * @return bool
     */
    public function rollback()
    {
        $this->_lastOperationSucceed = false;

        set_time_limit(0);
        ignore_user_abort(true);

        $rollbackWorker = $this->_useFtp ? new Mage_Backup_Filesystem_Rollback_Ftp($this)
            : new Mage_Backup_Filesystem_Rollback_Fs($this);
        $rollbackWorker->run();

        $this->_lastOperationSucceed = true;
    }

    /**
     * Implementation Create Backup functionality for Filesystem
     *
     * @throws Mage_Exception
     * @return boolean
     */
    public function create()
    {
        set_time_limit(0);
        ignore_user_abort(true);

        $this->_lastOperationSucceed = false;

        $this->_checkBackupsDir();

        $fsHelper = new Mage_Backup_Filesystem_Helper();

        $filesInfo = $fsHelper->getInfo(
            $this->getRootDir(),
            Mage_Backup_Filesystem_Helper::INFO_READABLE | Mage_Backup_Filesystem_Helper::INFO_SIZE,
            $this->getIgnorePaths()
        );

        if (!$filesInfo['readable']) {
            throw new Mage_Backup_Exception_NotEnoughPermissions('Not enough permissions to read files for backup');
        }

        $freeSpace = disk_free_space($this->getBackupsDir());

        if (2 * $filesInfo['size'] > $freeSpace) {
            throw new Mage_Backup_Exception_NotEnoughFreeSpace('Not enough free space to create backup');
        }

        $tarTmpPath = $this->_getTarTmpPath();

        $tarPacker = new Mage_Backup_Archive_Tar();
        $tarPacker->setSkipFiles($this->getIgnorePaths())
            ->pack($this->getRootDir(), $tarTmpPath, true);

        if (!is_file($tarTmpPath) || filesize($tarTmpPath) == 0) {
            throw new Mage_Exception('Failed to create backup');
        }

        $backupPath = $this->getBackupPath();

        $gzPacker = new Mage_Archive_Gz();
        $gzPacker->pack($tarTmpPath, $backupPath);

        if (!is_file($backupPath) || filesize($backupPath) == 0) {
            throw new Mage_Exception('Failed to create backup');
        }

        @unlink($tarTmpPath);

        $this->_lastOperationSucceed = true;
    }

    /**
     * Force class to use ftp for rollback procedure
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $path
     * @return Mage_Backup_Filesystem
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
     * @see Mage_Backup_Interface::getType()
     * @return string
     */
    public function getType()
    {
        return 'filesystem';
    }

    /**
     * Add path that should be ignoring when creating or rolling back backup
     *
     * @param string|array $paths
     * @return Mage_Backup_Filesystem
     */
    public function addIgnorePaths($paths)
    {
        if (is_string($paths)) {
            if (!in_array($paths, $this->_ignorePaths)) {
                $this->_ignorePaths[] = $paths;
            }
        }
        else if (is_array($paths)) {
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
     * @see Mage_Backup_Abstract::setBackupsDir()
     * @param string $backupsDir
     * @return Mage_Backup_Filesystem
     */
    public function setBackupsDir($backupsDir)
    {
        parent::setBackupsDir($backupsDir);
        $this->addIgnorePaths($backupsDir);
        return $this;
    }

    /**
     * getter for $_ftpPath variable
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
     * Check backups directory existance and whether it's writeable
     *
     * @throws Mage_Exception
     */
    protected function _checkBackupsDir()
    {
        $backupsDir = $this->getBackupsDir();

        if (!is_dir($backupsDir)) {
            $backupsDirParentDirectory = basename($backupsDir);

            if (!is_writeable($backupsDirParentDirectory)) {
                throw new Mage_Backup_Exception_NotEnoughPermissions('Cant create backups directory');
            }

            mkdir($backupsDir);
            chmod($backupsDir, 0777);
        }

        if (!is_writable($backupsDir)) {
            throw new Mage_Backup_Exception_NotEnoughPermissions('Backups directory is not writeable');
        }
    }

    /**
     * Generate tmp name for tarball
     */
    protected function _getTarTmpPath()
    {
        $tmpName = '~tmp-'. microtime(true) . '.tar';
        return $this->getBackupsDir() . DS . $tmpName;
    }
}
