<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Filesystem\Rollback;

use Magento\Framework\Backup\Exception\CantLoadSnapshot;
use Magento\Framework\Backup\Exception\FtpConnectionFailed;
use Magento\Framework\Backup\Exception\FtpValidationFailed;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Rollback worker for rolling back via ftp
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Ftp extends AbstractRollback
{
    /**
     * @var \Magento\Framework\System\Ftp
     */
    protected $_ftpClient;

    /**
     * Files rollback implementation via ftp
     *
     * @return void
     * @throws LocalizedException
     *
     * @see AbstractRollback::run()
     */
    public function run()
    {
        $snapshotPath = $this->_snapshot->getBackupPath();

        if (!is_file($snapshotPath) || !is_readable($snapshotPath)) {
            throw new CantLoadSnapshot(
                new Phrase('Can\'t load snapshot archive')
            );
        }

        $this->_initFtpClient();
        $this->_validateFtp();

        $tmpDir = $this->_createTmpDir();
        $this->_unpackSnapshot($tmpDir);

        $fsHelper = new Helper();

        $this->_cleanupFtp();
        $this->_uploadBackupToFtp($tmpDir);

        if ($this->_snapshot->keepSourceFile() === false) {
            $fsHelper->rm($tmpDir, [], true);
            $this->_ftpClient->delete($snapshotPath);
        }
    }

    /**
     * Initialize ftp client and connect to ftp
     *
     * @return void
     * @throws FtpConnectionFailed
     */
    protected function _initFtpClient()
    {
        try {
            $this->_ftpClient = new \Magento\Framework\System\Ftp();
            $this->_ftpClient->connect($this->_snapshot->getFtpConnectString());
        } catch (\Exception $e) {
            throw new FtpConnectionFailed(
                new Phrase($e->getMessage())
            );
        }
    }

    /**
     * Perform ftp validation. Check whether ftp account provided points to current magento installation
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _validateFtp()
    {
        $validationFilename = '~validation-' . microtime(true) . '.tmp';
        $validationFilePath = $this->_snapshot->getBackupsDir() . '/' . $validationFilename;

        $fh = @fopen($validationFilePath, 'w');
        @fclose($fh);

        if (!is_file($validationFilePath)) {
            throw new LocalizedException(
                new Phrase('Unable to validate ftp account')
            );
        }

        $rootDir = $this->_snapshot->getRootDir() ?? '';
        $ftpPath = $this->_snapshot->getFtpPath() . '/' . str_replace($rootDir, '', $validationFilePath);

        $fileExistsOnFtp = $this->_ftpClient->fileExists($ftpPath);
        @unlink($validationFilePath);

        if (!$fileExistsOnFtp) {
            throw new FtpValidationFailed(
                new Phrase('Failed to validate ftp account')
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
     * Method to create tmp dir.
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _createTmpDir()
    {
        $tmpDir = $this->_snapshot->getBackupsDir() . '/~tmp-' . microtime(true);

        $result = @mkdir($tmpDir);

        if (false === $result) {
            throw new \Magento\Framework\Backup\Exception\NotEnoughPermissions(
                new Phrase('Failed to create directory %1', [$tmpDir])
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
            // @phpstan-ignore-next-line
            $ftpPath = $this->_snapshot->getFtpPath() . '/' . str_replace($rootDir ?? '', '', $item->__toString());
            $ftpPath = str_replace('\\', '/', $ftpPath);

            $this->_ftpClient->delete($ftpPath);
        }
    }

    /**
     * Perform files rollback
     *
     * @param string $tmpDir
     * @return void
     * @throws LocalizedException
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
            // @phpstan-ignore-next-line
            $ftpPath = $this->_snapshot->getFtpPath() . '/' . str_replace($tmpDir ?? '', '', $item->__toString());
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
                        new Phrase('Failed to upload file %1 to ftp', [$item->__toString()])
                    );
                }
            }
        }
    }
}
