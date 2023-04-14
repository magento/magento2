<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class to work with archives
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractBackup implements BackupInterface, SourceFileInterface
{
    /**
     * Backup name
     *
     * @var string
     */
    protected $_name;

    /**
     * Backup creation date
     *
     * @var int
     */
    protected $_time;

    /**
     * Backup file extension
     *
     * @var string
     */
    protected $_backupExtension;

    /**
     * @var object
     */
    protected $_resourceModel;

    /**
     * Magento's root directory
     *
     * @var string
     */
    protected $_rootDir;

    /**
     * Path to directory where backups stored
     *
     * @var string
     */
    protected $_backupsDir;

    /**
     * Is last operation completed successfully
     *
     * @var bool
     */
    protected $_lastOperationSucceed = false;

    /**
     * Last failed operation error message
     *
     * @var string
     */
    protected $_lastErrorMessage;

    /**
     * Keep Source files in Backup
     *
     * @var boolean
     */
    private $keepSourceFile;

    /**
     * Set Backup Extension
     *
     * @param string $backupExtension
     * @return $this
     */
    public function setBackupExtension($backupExtension)
    {
        $this->_backupExtension = $backupExtension;
        return $this;
    }

    /**
     * Get Backup Extension
     *
     * @return string
     */
    public function getBackupExtension()
    {
        return $this->_backupExtension;
    }

    /**
     * Set Resource Model
     *
     * @param object $resourceModel
     * @return $this
     */
    public function setResourceModel($resourceModel)
    {
        $this->_resourceModel = $resourceModel;
        return $this;
    }

    /**
     * Get Resource Model
     *
     * @return object
     */
    public function getResourceModel()
    {
        return $this->_resourceModel;
    }

    /**
     * Set Time
     *
     * @param int $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->_time = $time;
        return $this;
    }

    /**
     * Get Time
     *
     * @return int
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     * Set root directory of Magento installation
     *
     * @param string $rootDir
     * @throws LocalizedException
     * @return $this
     */
    public function setRootDir($rootDir)
    {
        if (!is_dir($rootDir)) {
            throw new LocalizedException(
                new Phrase('Bad root directory')
            );
        }

        $this->_rootDir = rtrim($rootDir, '/');
        return $this;
    }

    /**
     * Get Magento's root directory
     *
     * @return string
     */
    public function getRootDir()
    {
        return $this->_rootDir;
    }

    /**
     * Set path to directory where backups stored
     *
     * @param string $backupsDir
     * @return $this
     */
    public function setBackupsDir($backupsDir)
    {
        $this->_backupsDir = $backupsDir !== null ? rtrim($backupsDir, '/') : '';
        return $this;
    }

    /**
     * Get path to directory where backups stored
     *
     * @return string
     */
    public function getBackupsDir()
    {
        return $this->_backupsDir;
    }

    /**
     * Get path to backup
     *
     * @return string
     */
    public function getBackupPath()
    {
        return $this->getBackupsDir() . '/' . $this->getBackupFilename();
    }

    /**
     * Get backup file name
     *
     * @return string
     */
    public function getBackupFilename()
    {
        $filename = $this->getTime() . '_' . $this->getType();

        $name = $this->getName();

        if (!empty($name)) {
            $filename .= '_' . $name;
        }

        $filename .= '.' . $this->getBackupExtension();

        return $filename;
    }

    /**
     * Check whether last operation completed successfully
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSuccess()
    {
        return $this->_lastOperationSucceed;
    }

    /**
     * Get last error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_lastErrorMessage;
    }

    /**
     * Set error message
     *
     * @param string $errorMessage
     * @return void
     */
    public function setErrorMessage($errorMessage)
    {
        $this->_lastErrorMessage = $errorMessage;
    }

    /**
     * Set backup name
     *
     * @param string $name
     * @param bool $applyFilter
     * @return $this
     */
    public function setName($name, $applyFilter = true)
    {
        if ($applyFilter) {
            $name = $this->_filterName($name);
        }
        $this->_name = $name;
        return $this;
    }

    /**
     * Get backup name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get backup display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->_name !== null ? str_replace('_', ' ', $this->_name) : '';
    }

    /**
     * Removes disallowed characters and replaces spaces with underscores
     *
     * @param string $name
     * @return string
     */
    protected function _filterName($name)
    {
        $name = $name !== null ? trim(preg_replace('/[^\da-zA-Z ]/', '', $name)) : '';
        $name = preg_replace('/\s{2,}/', ' ', $name);

        return str_replace(' ', '_', $name);
    }

    /**
     * Check if keep files of backup
     *
     * @return bool
     * @since 102.0.0
     */
    public function keepSourceFile()
    {
        return $this->keepSourceFile;
    }

    /**
     * Set if keep files of backup
     *
     * @param bool $keepSourceFile
     * @return $this
     * @since 102.0.0
     */
    public function setKeepSourceFile(bool $keepSourceFile)
    {
        $this->keepSourceFile = $keepSourceFile;
        return $this;
    }
}
