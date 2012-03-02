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
 * Rollback worker for rolling back via ftp
 *
 * @category    Mage
 * @package     Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Filesystem_Rollback_Ftp extends Mage_Backup_Filesystem_Rollback_Abstract
{
    /**
     * Ftp client
     *
     * @var Mage_System_Ftp
     */
    protected $_ftpClient;

    /**
     * Files rollback implementation via ftp
     *
     * @see Mage_Backup_Filesystem_Rollback_Abstract::run()
     * @throws Mage_Exception
     */
    public function run()
    {
        $snapshotPath = $this->_snapshot->getBackupPath();

        if (!is_file($snapshotPath) || !is_readable($snapshotPath)) {
            throw new Mage_Backup_Exception_CantLoadSnapshot('Cant load snapshot archive');
        }

        $this->_initFtpClient();
        $this->_validateFtp();

        $tmpDir = $this->_createTmpDir();
        $this->_unpackSnapshot($tmpDir);

        $fsHelper = new Mage_Backup_Filesystem_Helper();

        $this->_cleanupFtp();
        $this->_uploadBackupToFtp($tmpDir);

        $fsHelper->rm($tmpDir, array(), true);
    }

    /**
     * Initialize ftp client and connect to ftp
     *
     * @throws Mage_Backup_Exception_FtpConnectionFailed
     */
    protected function _initFtpClient()
    {
        try {
            $this->_ftpClient = new Mage_System_Ftp();
            $this->_ftpClient->connect($this->_snapshot->getFtpConnectString());
        } catch (Exception $e) {
            throw new Mage_Backup_Exception_FtpConnectionFailed($e->getMessage());
        }
    }

    /**
     * Perform ftp validation. Check whether ftp account provided points to current magento installation
     *
     * @throws Mage_Exception
     */
    protected function _validateFtp()
    {
        $validationFilename = '~validation-' . microtime(true) . '.tmp';
        $validationFilePath = $this->_snapshot->getBackupsDir() . DS . $validationFilename;

        $fh = @fopen($validationFilePath, 'w');
        @fclose($fh);

        if (!is_file($validationFilePath)) {
            throw new Mage_Exception('Unable to validate ftp account');
        }

        $rootDir = $this->_snapshot->getRootDir();
        $ftpPath = $this->_snapshot->getFtpPath() . DS . str_replace($rootDir, '', $validationFilePath);

        $fileExistsOnFtp = $this->_ftpClient->fileExists($ftpPath);
        @unlink($validationFilePath);

        if (!$fileExistsOnFtp) {
            throw new Mage_Backup_Exception_FtpValidationFailed('Failed to validate ftp account');
        }
    }

    /**
     * Unpack snapshot
     *
     * @param string $tmpDir
     */
    protected function _unpackSnapshot($tmpDir)
    {
        $snapshotPath = $this->_snapshot->getBackupPath();

        $archiver = new Mage_Archive();
        $archiver->unpack($snapshotPath, $tmpDir);
    }

    /**
     * @throws Mage_Exception
     * @return string
     */
    protected function _createTmpDir()
    {
        $tmpDir = $this->_snapshot->getBackupsDir() . DS . '~tmp-' . microtime(true);

        $result = @mkdir($tmpDir);

        if (false === $result) {
            throw new Mage_Backup_Exception_NotEnoughPermissions('Failed to create directory ' . $tmpDir);
        }

        return $tmpDir;
    }

    /**
     * Delete magento and all files from ftp
     */
    protected function _cleanupFtp()
    {
        $rootDir = $this->_snapshot->getRootDir();

        $filesystemIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootDir), RecursiveIteratorIterator::CHILD_FIRST
        );

        $iterator = new Mage_Backup_Filesystem_Iterator_Filter($filesystemIterator, $this->_snapshot->getIgnorePaths());

        foreach ($iterator as $item) {
            $ftpPath = $this->_snapshot->getFtpPath() . DS . str_replace($rootDir, '', $item->__toString());
            $ftpPath = str_replace(DS, '/', $ftpPath);

            $this->_ftpClient->delete($ftpPath);
        }
    }

    /**
     * Perform files rollback
     *
     * @param string $tmpDir
     * @throws Mage_Exception
     */
    protected function _uploadBackupToFtp($tmpDir)
    {
        $filesystemIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmpDir), RecursiveIteratorIterator::SELF_FIRST
        );

        $iterator = new Mage_Backup_Filesystem_Iterator_Filter($filesystemIterator, $this->_snapshot->getIgnorePaths());

        foreach ($filesystemIterator as $item) {
            $ftpPath = $this->_snapshot->getFtpPath() . DS . str_replace($tmpDir, '', $item->__toString());
            $ftpPath = str_replace(DS, '/', $ftpPath);

            if ($item->isDir()) {
                $this->_ftpClient->mkdirRecursive($ftpPath);
            } else {
                $result = $this->_ftpClient->put($ftpPath, $item->__toString());
                if (false === $result) {
                    throw new Mage_Backup_Exception_NotEnoughPermissions('Failed to upload file '
                        . $item->__toString() . ' to ftp');
                }
            }
        }
    }
}
