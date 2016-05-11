<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Filesystem\Rollback;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Rollback worker for rolling back via ftp
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Ftp extends AbstractRollback
{
    /**
     * Ftp client
     *
     * @var \Magento\Framework\System\Ftp
     */
    protected $_ftpClient;

    /**
     * Files rollback implementation via ftp
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see AbstractRollback::run()
     */
    public function run()
    {
        $snapshotPath = $this->_snapshot->getBackupPath();

        if (!is_file($snapshotPath) || !is_readable($snapshotPath)) {
            throw new \Magento\Framework\Backup\Exception\CantLoadSnapshot(
                new \Magento\Framework\Phrase('Can\'t load snapshot archive')
            );
        }

        $this->_initFtpClient();
        $this->_validateFtp();

        $tmpDir = $this->_createTmpDir();
        $this->_unpackSnapshot($tmpDir);

        $fsHelper = new \Magento\Framework\Backup\Filesystem\Helper();

        $this->_cleanupFtp();
        $this->_uploadBackupToFtp($tmpDir);

        $fsHelper->rm($tmpDir, [], true);
    }

    /**
     * Initialize ftp client and connect to ftp
     *
     * @return void
     * @throws \Magento\Framework\Backup\Exception\FtpConnectionFailed
     */
    protected function _initFtpClient()
    {
        try {
            $this->_ftpClient = new \Magento\Framework\System\Ftp();
            $this->_ftpClient->connect($this->_snapshot->getFtpConnectString());
        } catch (\Exception $e) {
            throw new \Magento\Framework\Backup\Exception\FtpConnectionFailed(
                new \Magento\Framework\Phrase($e->getMessage())
            );
        }
    }

    /**
     * Perform ftp validation. Check whether ftp account provided points to current magento installation
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _validateFtp()
    {
        $validationFilename = '~validation-' . microtime(true) . '.tmp';
        $validationFilePath = $this->_snapshot->getBackupsDir() . '/' . $validationFilename;

        $fh = @fopen($validationFilePath, 'w');
        @fclose($fh);

        if (!is_file($validationFilePath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Unable to validate ftp account')
            );
        }

        $rootDir = $this->_snapshot->getRootDir();
        $ftpPath = $this->_snapshot->getFtpPath() . '/' . str_replace($rootDir, '', $validationFilePath);

        $fileExistsOnFtp = $this->_ftpClient->fileExists($ftpPath);
        @unlink($validationFilePath);

        if (!$fileExistsOnFtp) {
            throw new \Magento\Framework\Backup\Exception\FtpValidationFailed(
                new \Magento\Framework\Phrase('Failed to validate ftp account')
            );
        }
    }

    /**
     * Unpack snapshot
     *
     * @param string $tmpDir
     * @return void
     */
    protected function _unpackSnapshot($tmpDir)
    {
        $snapshotPath = $this->_snapshot->getBackupPath();

        $archiver = new \Magento\Framework\Archive();
        $archiver->unpack($snapshotPath, $tmpDir);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _createTmpDir()
    {
        $tmpDir = $this->_snapshot->getBackupsDir() . '/~tmp-' . microtime(true);

        $result = @mkdir($tmpDir);

        if (false === $result) {
            throw new \Magento\Framework\Backup\Exception\NotEnoughPermissions(
                new \Magento\Framework\Phrase('Failed to create directory %1', [$tmpDir])
            );
        }

        return $tmpDir;
    }

    /**
     * Delete magento and all files from ftp
     *
     * @return void
     */
    protected function _cleanupFtp()
    {
        $rootDir = $this->_snapshot->getRootDir();

        $filesystemIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootDir),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $iterator = new \Magento\Framework\Backup\Filesystem\Iterator\Filter(
            $filesystemIterator,
            $this->_snapshot->getIgnorePaths()
        );

        foreach ($iterator as $item) {
            $ftpPath = $this->_snapshot->getFtpPath() . '/' . str_replace($rootDir, '', $item->__toString());
            $ftpPath = str_replace('\\', '/', $ftpPath);

            $this->_ftpClient->delete($ftpPath);
        }
    }

    /**
     * Perform files rollback
     *
     * @param string $tmpDir
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _uploadBackupToFtp($tmpDir)
    {
        $filesystemIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tmpDir),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $iterator = new \Magento\Framework\Backup\Filesystem\Iterator\Filter(
            $filesystemIterator,
            $this->_snapshot->getIgnorePaths()
        );

        foreach ($filesystemIterator as $item) {
            $ftpPath = $this->_snapshot->getFtpPath() . '/' . str_replace($tmpDir, '', $item->__toString());
            $ftpPath = str_replace('\\', '/', $ftpPath);

            if ($item->isLink()) {
                continue;
            }

            if ($item->isDir()) {
                $this->_ftpClient->mkdirRecursive($ftpPath);
            } else {
                $result = $this->_ftpClient->put($ftpPath, $item->__toString());
                if (false === $result) {
                    throw new \Magento\Framework\Backup\Exception\NotEnoughPermissions(
                        new \Magento\Framework\Phrase('Failed to upload file %1 to ftp', [$item->__toString()])
                    );
                }
            }
        }
    }
}
